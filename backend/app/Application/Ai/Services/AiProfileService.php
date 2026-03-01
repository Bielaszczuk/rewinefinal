<?php

namespace App\Application\Ai\Services;

use App\Models\Wine;
use App\Models\WineAiProfile;
use Illuminate\Support\Facades\Log;
use OpenAI;

class AiProfileService
{
    public function getOrGenerate(string $wineId, string $language): array
    {
        Wine::findOrFail($wineId);

        $profile = WineAiProfile::where('wine_id', $wineId)
            ->where('language', $language)
            ->first();

        if ($profile) {
            return $this->formatResponse($profile, true);
        }

        return $this->generate($wineId, $language);
    }

    public function forceRegenerate(string $wineId, string $language): array
    {
        WineAiProfile::where('wine_id', $wineId)->where('language', $language)->delete();
        return $this->generate($wineId, $language);
    }

    public function getStatus(string $wineId, string $language): array
    {
        $profile = WineAiProfile::where('wine_id', $wineId)->where('language', $language)->first();
        return [
            'wineId'      => $wineId,
            'language'    => $language,
            'status'      => $profile ? 'GENERATED' : 'NONE',
            'generatedAt' => $profile?->updated_at?->toISOString(),
        ];
    }

    private function generate(string $wineId, string $language): array
    {
        $wine = Wine::with('winery')->findOrFail($wineId);

        $useOpenAI = config('app.ai_provider', 'mock') === 'openai'
            && config('app.ai_enabled', false)
            && !empty(config('services.openai.key'));

        $profileData = $useOpenAI
            ? $this->openAiGenerate($wine, $language)
            : $this->mockGenerate($wine, $language);

        $profile = WineAiProfile::updateOrCreate(
            ['wine_id' => $wineId, 'language' => $language],
            ['profile_json' => $profileData]
        );

        return $this->formatResponse($profile, false);
    }

    private function openAiGenerate(Wine $wine, string $language): array
    {
        $isSpanish = str_starts_with($language, 'es');
        $langLabel = $isSpanish ? 'Spanish (Argentina)' : 'English';

        $wineInfo = implode("\n", array_filter([
            "Name: {$wine->name}",
            "Type: " . strtolower($wine->wine_type ?? ''),
            "Vintage: {$wine->vintage}",
            "Winery: " . ($wine->winery?->name ?? ''),
            "Region: " . ($wine->winery?->region ?? ''),
            "Country: " . ($wine->winery?->country ?? ''),
            $wine->description_es ? "Description: {$wine->description_es}" : null,
            $wine->grapes ? "Grapes: " . implode(', ', (array)$wine->grapes) : null,
            $wine->alcohol_content ? "Alcohol: {$wine->alcohol_content}%" : null,
        ]));

        $prompt = <<<PROMPT
You are an expert sommelier. Create a professional wine analysis in {$langLabel} focusing on sensory and technical characteristics.

Wine: {$wineInfo}

Return ONLY valid JSON with this structure:

{
  "summary": "2-3 sentences capturing the wine's essence, terroir influence, and overall character.",

  "visualAnalysis": {
    "color": "Precise color description (e.g., deep ruby, pale straw, salmon pink)",
    "clarity": "Crystal clear/Bright/Slightly hazy",
    "intensity": "Pale/Medium/Deep/Opaque",
    "viscosity": "Description of legs/tears indicating body and alcohol"
  },

  "aromaticProfile": {
    "intensity": "Subtle/Moderate/Pronounced/Powerful (with brief description)",
    "primaryAromas": ["Aroma 1", "Aroma 2", "Aroma 3"],
    "secondaryAromas": ["Fermentation aroma 1", "Oak/aging note"],
    "bouquet": "1-2 sentences on aromatic complexity and integration"
  },

  "palateAnalysis": {
    "sweetness": "Bone dry/Dry/Off-dry/Medium/Sweet with description",
    "acidity": "Low/Medium-/Medium/Medium+/High - describe the sensation",
    "tannin": "None/Low/Medium/High - describe texture (silky/chalky/grippy)",
    "alcohol": "Low/Medium/High - perceived warmth",
    "body": "Light/Medium-/Medium/Medium+/Full - weight on palate",
    "texture": "Description of mouthfeel",
    "balance": "How components integrate"
  },

  "flavorProfile": {
    "primaryFlavors": ["Flavor 1", "Flavor 2", "Flavor 3"],
    "secondaryFlavors": ["Oak/aging flavor 1", "Flavor 2"],
    "intensity": "Light/Moderate/Pronounced",
    "finish": "Short/Medium/Long - describe lingering characteristics"
  },

  "foodPairings": {
    "pairings": ["Dish 1", "Dish 2", "Dish 3", "Dish 4"],
    "explanation": "Brief explanation of pairing principles"
  },

  "servingRecommendations": {
    "temperature": "Optimal temp (e.g., 16-18°C)",
    "glassware": "Glass type",
    "decanting": "Yes/No and duration",
    "agingPotential": "Drink now / Age 2-5 years / etc."
  }
}

Be precise, technical, and professional. Focus on what a sommelier would analyze.
PROMPT;

        try {
            $client = OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->withHttpClient(new \GuzzleHttp\Client([
                    'timeout' => 45,
                ]))
                ->make();

            $response = $client->chat()->create([
                'model'       => config('services.openai.model', 'gpt-4o-mini'),
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.7,
                'max_tokens'  => 1200,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content ?? '{}';
            $data    = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                return $this->mockGenerate($wine, $language);
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('OpenAI AiProfile failed, falling back to mock: ' . $e->getMessage());
            return $this->mockGenerate($wine, $language);
        }
    }

    private function formatResponse(WineAiProfile $profile, bool $cached): array
    {
        $data = $profile->profile_json;
        return [
            'wineId'         => $profile->wine_id,
            'language'       => $profile->language,
            'generatedAt'    => $profile->updated_at->toISOString(),
            'cached'         => $cached,
            'profileContent' => [
                'summary'                  => $data['summary'] ?? null,
                'visualAnalysis'           => $data['visualAnalysis'] ?? null,
                'aromaticProfile'          => $data['aromaticProfile'] ?? null,
                'palateAnalysis'           => $data['palateAnalysis'] ?? null,
                'flavorProfile'            => $data['flavorProfile'] ?? null,
                'foodPairings'             => $data['foodPairings'] ?? null,
                'servingRecommendations'   => $data['servingRecommendations'] ?? null,
                'tastingNotes'             => $data['tastingNotes'] ?? null,
                'sensoryProfile'           => $data['sensoryProfile'] ?? null,
                'idealOccasions'           => $data['occasions'] ?? [],
            ],
            'summary'                => $data['summary'] ?? null,
            'visualAnalysis'         => $data['visualAnalysis'] ?? null,
            'aromaticProfile'        => $data['aromaticProfile'] ?? null,
            'palateAnalysis'         => $data['palateAnalysis'] ?? null,
            'flavorProfile'          => $data['flavorProfile'] ?? null,
            'foodPairings'           => is_array($data['foodPairings'] ?? null) && isset($data['foodPairings']['pairings'])
                ? $data['foodPairings']
                : ['pairings' => $data['foodPairings'] ?? [], 'explanation' => null],
            'servingRecommendations' => $data['servingRecommendations'] ?? null,
        ];
    }

    private function mockGenerate(Wine $wine, string $language): array
    {
        $isSpanish = str_starts_with($language, 'es');
        $type      = strtolower($wine->wine_type ?? 'red');
        $name      = $wine->name ?? 'Wine';
        $region    = $wine->winery?->region ?? ($isSpanish ? 'la región' : 'the region');
        $country   = $wine->winery?->country ?? ($isSpanish ? 'el país' : 'the country');
        $vintage   = $wine->vintage ?? date('Y') - 3;

        if ($isSpanish) {
            $typeDesc  = ['red'=>'tinto robusto','white'=>'blanco fresco','rose'=>'rosado elegante','sparkling'=>'espumante festivo'][$type] ?? 'vino especial';
            $summary   = "El $name es un $typeDesc de $region, $country, cosecha $vintage. Presenta una expresión auténtica del terroir local con carácter único y elegante.";

            $visual = match($type) {
                'red' => ['color'=>'Rojo rubí intenso','clarity'=>'Cristalino','intensity'=>'Profundo','viscosity'=>'Lágrimas pronunciadas indicando cuerpo medio-alto'],
                'white' => ['color'=>'Amarillo pálido con reflejos verdosos','clarity'=>'Brillante','intensity'=>'Medio','viscosity'=>'Lágrimas finas'],
                'rose' => ['color'=>'Rosa salmón','clarity'=>'Cristalino','intensity'=>'Medio','viscosity'=>'Lágrimas delicadas'],
                default => ['color'=>'Color característico','clarity'=>'Brillante','intensity'=>'Medio','viscosity'=>'Lágrimas normales'],
            };

            $aromatic = match($type) {
                'red' => ['intensity'=>'Pronunciada','primaryAromas'=>['Frutos rojos maduros','Ciruela','Especias'],'secondaryAromas'=>['Vainilla','Roble tostado'],'bouquet'=>'Aromas bien integrados con notas de fruta madura y toques especiados del roble.'],
                'white' => ['intensity'=>'Moderada','primaryAromas'=>['Cítricos','Manzana verde','Flores blancas'],'secondaryAromas'=>['Levadura','Mantequilla'],'bouquet'=>'Perfil aromático fresco y limpio con buena complejidad.'],
                'rose' => ['intensity'=>'Moderada','primaryAromas'=>['Frutillas','Sandía','Cítricos'],'secondaryAromas'=>['Notas florales'],'bouquet'=>'Aromas frescos y delicados con carácter frutal predominante.'],
                default => ['intensity'=>'Moderada','primaryAromas'=>['Frutas','Notas florales'],'secondaryAromas'=>['Fermentación'],'bouquet'=>'Perfil aromático equilibrado.'],
            };

            $palate = match($type) {
                'red' => ['sweetness'=>'Seco','acidity'=>'Media+ - acidez vibrante que da frescura','tannin'=>'Medio - taninos sedosos y bien integrados','alcohol'=>'Medio - 13-14% sin sensación cálida','body'=>'Medio-Plus - peso sustancial en boca','texture'=>'Sedoso con estructura firme','balance'=>'Excelente equilibrio entre fruta, acidez y taninos'],
                'white' => ['sweetness'=>'Seco','acidity'=>'Alta - acidez crujiente y refrescante','tannin'=>'Ninguno','alcohol'=>'Medio - ligero y fresco','body'=>'Medio - peso moderado','texture'=>'Cremoso con buena frescura','balance'=>'Acidez y fruta bien balanceadas'],
                default => ['sweetness'=>'Seco','acidity'=>'Media','tannin'=>'Bajo','alcohol'=>'Medio','body'=>'Medio','texture'=>'Suave','balance'=>'Equilibrado'],
            };

            $flavor = match($type) {
                'red' => ['primaryFlavors'=>['Cereza negra','Ciruela','Especias'],'secondaryFlavors'=>['Vainilla','Chocolate'],'intensity'=>'Pronunciada','finish'=>'Largo - persisten sabores de fruta madura y especias por más de 30 segundos'],
                'white' => ['primaryFlavors'=>['Limón','Manzana','Pera'],'secondaryFlavors'=>['Mantequilla','Avellana'],'intensity'=>'Moderada','finish'=>'Medio - final fresco y limpio'],
                default => ['primaryFlavors'=>['Frutas'],'secondaryFlavors'=>['Notas secundarias'],'intensity'=>'Moderada','finish'=>'Medio'],
            };

            $foodPairings = ['pairings'=>match($type) {
                'red' => ['Carnes rojas a la parrilla','Cordero asado','Quesos curados','Pasta con ragú'],
                'white' => ['Pescado grillado','Mariscos','Pollo a la crema','Ensaladas'],
                'rose' => ['Salmón','Ensaladas','Sushi','Quesos suaves'],
                default => ['Quesos','Embutidos'],
            },'explanation'=>'Los maridajes se basan en complementar la estructura del vino con la intensidad de los platos.'];

            $serving = ['temperature'=>match($type){'red'=>'16-18°C','white'=>'8-12°C','sparkling'=>'6-8°C',default=>'14-16°C'},'glassware'=>match($type){'red'=>'Copa bordelesa','white'=>'Copa de vino blanco',default=>'Copa estándar'},'decanting'=>$type==='red'?'Sí, 30 minutos':'No necesario','agingPotential'=>'Beber ahora o guardar 2-5 años'];
        } else {
            $typeDesc  = ['red'=>'robust red','white'=>'fresh white','rose'=>'elegant rosé','sparkling'=>'festive sparkling'][$type] ?? 'special wine';
            $summary   = "$name is a $typeDesc from $region, $country, vintage $vintage. It showcases an authentic expression of local terroir with unique and elegant character.";

            $visual = match($type) {
                'red' => ['color'=>'Deep ruby red','clarity'=>'Crystal clear','intensity'=>'Deep','viscosity'=>'Pronounced legs indicating medium-full body'],
                'white' => ['color'=>'Pale yellow with greenish hints','clarity'=>'Bright','intensity'=>'Medium','viscosity'=>'Fine legs'],
                'rose' => ['color'=>'Salmon pink','clarity'=>'Crystal clear','intensity'=>'Medium','viscosity'=>'Delicate legs'],
                default => ['color'=>'Characteristic color','clarity'=>'Bright','intensity'=>'Medium','viscosity'=>'Normal legs'],
            };

            $aromatic = match($type) {
                'red' => ['intensity'=>'Pronounced','primaryAromas'=>['Ripe red fruits','Plum','Spices'],'secondaryAromas'=>['Vanilla','Toasted oak'],'bouquet'=>'Well-integrated aromas with ripe fruit notes and spicy oak touches.'],
                'white' => ['intensity'=>'Moderate','primaryAromas'=>['Citrus','Green apple','White flowers'],'secondaryAromas'=>['Yeast','Butter'],'bouquet'=>'Fresh and clean aromatic profile with good complexity.'],
                'rose' => ['intensity'=>'Moderate','primaryAromas'=>['Strawberry','Watermelon','Citrus'],'secondaryAromas'=>['Floral notes'],'bouquet'=>'Fresh and delicate aromas with predominant fruity character.'],
                default => ['intensity'=>'Moderate','primaryAromas'=>['Fruits','Floral notes'],'secondaryAromas'=>['Fermentation'],'bouquet'=>'Balanced aromatic profile.'],
            };

            $palate = match($type) {
                'red' => ['sweetness'=>'Dry','acidity'=>'Medium+ - vibrant acidity providing freshness','tannin'=>'Medium - silky and well-integrated tannins','alcohol'=>'Medium - 13-14% without warmth','body'=>'Medium-Plus - substantial weight','texture'=>'Silky with firm structure','balance'=>'Excellent balance between fruit, acidity and tannins'],
                'white' => ['sweetness'=>'Dry','acidity'=>'High - crisp and refreshing acidity','tannin'=>'None','alcohol'=>'Medium - light and fresh','body'=>'Medium - moderate weight','texture'=>'Creamy with good freshness','balance'=>'Well-balanced acidity and fruit'],
                default => ['sweetness'=>'Dry','acidity'=>'Medium','tannin'=>'Low','alcohol'=>'Medium','body'=>'Medium','texture'=>'Smooth','balance'=>'Balanced'],
            };

            $flavor = match($type) {
                'red' => ['primaryFlavors'=>['Black cherry','Plum','Spices'],'secondaryFlavors'=>['Vanilla','Chocolate'],'intensity'=>'Pronounced','finish'=>'Long - ripe fruit and spice flavors persist for over 30 seconds'],
                'white' => ['primaryFlavors'=>['Lemon','Apple','Pear'],'secondaryFlavors'=>['Butter','Hazelnut'],'intensity'=>'Moderate','finish'=>'Medium - fresh and clean finish'],
                default => ['primaryFlavors'=>['Fruits'],'secondaryFlavors'=>['Secondary notes'],'intensity'=>'Moderate','finish'=>'Medium'],
            };

            $foodPairings = ['pairings'=>match($type) {
                'red' => ['Grilled red meats','Roasted lamb','Aged cheeses','Pasta with ragù'],
                'white' => ['Grilled fish','Seafood','Creamy chicken','Salads'],
                'rose' => ['Salmon','Salads','Sushi','Soft cheeses'],
                default => ['Cheeses','Charcuterie'],
            },'explanation'=>'Pairings are based on complementing the wine structure with dish intensity.'];

            $serving = ['temperature'=>match($type){'red'=>'16-18°C','white'=>'8-12°C','sparkling'=>'6-8°C',default=>'14-16°C'},'glassware'=>match($type){'red'=>'Bordeaux glass','white'=>'White wine glass',default=>'Standard glass'},'decanting'=>$type==='red'?'Yes, 30 minutes':'Not necessary','agingPotential'=>'Drink now or age 2-5 years'];
        }

        return [
            'summary' => $summary,
            'visualAnalysis' => $visual,
            'aromaticProfile' => $aromatic,
            'palateAnalysis' => $palate,
            'flavorProfile' => $flavor,
            'foodPairings' => $foodPairings,
            'servingRecommendations' => $serving,
        ];
    }
}

