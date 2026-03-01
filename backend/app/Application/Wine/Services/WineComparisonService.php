<?php

namespace App\Application\Wine\Services;

use App\Domain\Wine\Contracts\WineRepositoryInterface;
use App\Models\Wine;
use App\Models\WineComparison;
use OpenAI;

class WineComparisonService
{
    public function __construct(
        private readonly WineRepositoryInterface $wineRepository
    ) {}

    public function compare(string $wineAId, string $wineBId, string $language): array
    {
        [$idA, $idB] = strcmp($wineAId, $wineBId) <= 0
            ? [$wineAId, $wineBId]
            : [$wineBId, $wineAId];

        $wineA = $this->wineRepository->findByIdOrFail($wineAId);
        $wineB = $this->wineRepository->findByIdOrFail($wineBId);

        $cached = WineComparison::where('wine_a_id', $idA)
            ->where('wine_b_id', $idB)
            ->where('language', $language)
            ->first();

        if ($cached) {
            return $this->formatResponse($cached, $wineA, $wineB, true);
        }

        $useOpenAI = config('app.ai_provider', 'mock') === 'openai'
            && config('app.ai_enabled', false)
            && !empty(config('services.openai.key'));

        $comparisonData = $useOpenAI
            ? $this->openAiCompare($wineA, $wineB, $language)
            : $this->mockCompare($wineA, $wineB, $language);

        $comparison = WineComparison::create([
            'wine_a_id'       => $idA,
            'wine_b_id'       => $idB,
            'language'        => $language,
            'comparison_json' => $comparisonData,
        ]);

        return $this->formatResponse($comparison, $wineA, $wineB, false);
    }

    private function openAiCompare(Wine $wineA, Wine $wineB, string $language): array
    {
        $isSpanish = str_starts_with($language, 'es');
        $langLabel = $isSpanish ? 'Spanish (Argentina)' : 'English';

        $wineInfo = fn(Wine $w) => implode(', ', array_filter([
            $w->name,
            strtolower($w->wine_type ?? ''),
            $w->vintage ? "vintage {$w->vintage}" : null,
            $w->winery?->name,
            $w->winery?->region,
            $w->winery?->country,
        ]));

        $prompt = <<<PROMPT
You are an expert sommelier comparing wines. Analyze in {$langLabel}.

Wine A: {$wineInfo($wineA)}
Wine B: {$wineInfo($wineB)}

Return ONLY valid JSON:

{
  "summary": "2-3 sentences: character of each wine and main difference.",

  "keyDifferences": [
    "Major structural difference",
    "Flavor profile distinction",
    "Terroir/style difference"
  ],

  "similarities": [
    "Shared characteristic 1",
    "Shared characteristic 2"
  ],

  "attributeComparison": {
    "attributes": [
      {"name": "Body", "wineAValue": "Full/Medium/Light", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100},
      {"name": "Tannins", "wineAValue": "High/Medium/Low", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100},
      {"name": "Acidity", "wineAValue": "High/Medium/Low", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100},
      {"name": "Fruit Intensity", "wineAValue": "Intense/Moderate/Subtle", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100},
      {"name": "Complexity", "wineAValue": "High/Moderate/Simple", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100},
      {"name": "Finish", "wineAValue": "Long/Medium/Short", "wineBValue": "...", "wineAScore": 0-100, "wineBScore": 0-100}
    ]
  },

  "bestFor": {
    "wineA": ["Occasion 1", "Occasion 2"],
    "wineB": ["Occasion 1", "Occasion 2"]
  },

  "recommendation": "1-2 paragraphs: when to choose each wine and why. Be specific about occasions and preferences."
}

Be concise and analytical.
PROMPT;

        try {
            $client = OpenAI::factory()
                ->withApiKey(config('services.openai.key'))
                ->withHttpClient(new \GuzzleHttp\Client([
                    'timeout' => 45,
                ]))
                ->make();

            $response = $client->chat()->create([
                'model'           => config('services.openai.model', 'gpt-4o-mini'),
                'messages'        => [['role' => 'user', 'content' => $prompt]],
                'temperature'     => 0.7,
                'max_tokens'      => 1000,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content ?? '{}';
            $data    = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                return $this->mockCompare($wineA, $wineB, $language);
            }

            return $data;
        } catch (\Throwable $e) {
            \Log::warning('OpenAI Comparison failed, falling back to mock: ' . $e->getMessage());
            return $this->mockCompare($wineA, $wineB, $language);
        }
    }

    private function formatResponse(WineComparison $comp, Wine $wineA, Wine $wineB, bool $cached): array
    {
        $data = $comp->comparison_json;
        return [
            'wineAId'    => $wineA->id,
            'wineAName'  => $wineA->name,
            'wineBId'    => $wineB->id,
            'wineBName'  => $wineB->name,
            'language'   => $comp->language,
            'generatedAt'=> $comp->updated_at->toISOString(),
            'cached'     => $cached,
            'summary'    => $data['summary'] ?? null,
            'keyDifferences'      => $data['keyDifferences'] ?? $data['differences'] ?? [],
            'similarities'        => $data['similarities'] ?? [],
            'attributeComparison' => $data['attributeComparison'] ?? null,
            'bestFor'             => $data['bestFor'] ?? null,
            'recommendation'      => $data['recommendation'] ?? null,
            'comparisonContent'   => [
                'similarities'       => $data['similarities'] ?? [],
                'keyDifferences'     => $data['keyDifferences'] ?? $data['differences'] ?? [],
                'recommendation'     => $data['recommendation'] ?? null,
                'summary'            => $data['summary'] ?? null,
                'bestFor'            => $data['bestFor'] ?? null,
            ],
        ];
    }

    private function mockCompare(Wine $wineA, Wine $wineB, string $language): array
    {
        $isSpanish = str_starts_with($language, 'es');
        $nameA = $wineA->name;
        $nameB = $wineB->name;

        if ($isSpanish) {
            $summary        = "Comparación profesional entre $nameA y $nameB. El primero destaca por su estructura robusta y complejidad, mientras que el segundo ofrece elegancia y frescura. Ambos representan expresiones auténticas de sus respectivos terroirs y estilos enológicos, pero cada uno brilla en contextos diferentes.";
            $similarities   = ["Ambos son vinos de calidad premium con carácter regional bien definido", "Comparten notas frutales equilibradas y buena estructura", "Ambos tienen potencial de guarda y evolucionarán positivamente"];
            $keyDifferences = ["$nameA presenta mayor cuerpo, taninos más pronunciados y mayor intensidad aromática", "$nameB ofrece mayor frescura, acidez más marcada y perfil más delicado", "$nameA es ideal para carnes rojas y platos contundentes, mientras $nameB prefiere pescados y aves", "$nameA tiene mayor potencial de guarda (8-12 años) vs $nameB (3-5 años)", "Diferente filosofía enológica: $nameA busca potencia y estructura, $nameB busca fineza y expresión frutal"];
            $recommendation = "Para ocasiones formales con carnes rojas a la parrilla, asados o guisos intensos, $nameA es la elección perfecta gracias a sus taninos robustos y cuerpo completo que complementan la grasa y proteínas. Es también ideal para coleccionistas que buscan vinos de guarda.\n\n$nameB, por su parte, destaca en almuerzos casuales, maridajes con pescados grasos, aves de corral o risottos. Su frescura y acidez equilibrada lo hacen versátil y muy amigable para quienes prefieren vinos más accesibles y elegantes.\n\nAmbos son excelentes representantes de sus estilos, pero la elección depende del contexto: poder y estructura ($nameA) versus elegancia y versatilidad ($nameB).";
            $bestFor = [
                'wineA' => ["Asado argentino o parrillada completa", "Cenas formales y celebraciones especiales", "Inversión en bodega personal (guarda 8-12 años)"],
                'wineB' => ["Almuerzo de domingo con familia", "Maridaje con salmón, atún o risotto", "Vino versátil para diversas ocasiones"]
            ];
        } else {
            $summary        = "Professional comparison between $nameA and $nameB. The first stands out for its robust structure and complexity, while the second offers elegance and freshness. Both represent authentic expressions of their respective terroirs and winemaking styles, but each shines in different contexts.";
            $similarities   = ["Both are premium quality wines with well-defined regional character", "They share balanced fruity notes and good structure", "Both have aging potential and will evolve positively"];
            $keyDifferences = ["$nameA has more body, more pronounced tannins and greater aromatic intensity", "$nameB offers greater freshness, more marked acidity and a more delicate profile", "$nameA is ideal for red meats and hearty dishes, while $nameB prefers fish and poultry", "$nameA has greater aging potential (8-12 years) vs $nameB (3-5 years)", "Different winemaking philosophy: $nameA seeks power and structure, $nameB seeks finesse and fruit expression"];
            $recommendation = "For formal occasions with grilled red meats, roasts or intense stews, $nameA is the perfect choice thanks to its robust tannins and full body that complement fat and proteins. It's also ideal for collectors seeking cellar-worthy wines.\n\n$nameB, on the other hand, excels at casual lunches, pairings with fatty fish, poultry or risottos. Its freshness and balanced acidity make it versatile and very approachable for those who prefer more accessible and elegant wines.\n\nBoth are excellent representatives of their styles, but the choice depends on context: power and structure ($nameA) versus elegance and versatility ($nameB).";
            $bestFor = [
                'wineA' => ["Argentine asado or full barbecue", "Formal dinners and special celebrations", "Personal cellar investment (aging 8-12 years)"],
                'wineB' => ["Sunday family lunch", "Pairing with salmon, tuna or risotto", "Versatile wine for various occasions"]
            ];
        }

        return [
            'summary' => $summary,
            'similarities' => $similarities,
            'keyDifferences' => $keyDifferences,
            'recommendation' => $recommendation,
            'bestFor' => $bestFor,
            'attributeComparison' => [
                'attributes' => [
                    ['name' => 'Body', 'wineAValue' => 'Full', 'wineBValue' => 'Medium', 'wineAScore' => 90, 'wineBScore' => 65],
                    ['name' => 'Tannins', 'wineAValue' => 'High', 'wineBValue' => 'Medium', 'wineAScore' => 85, 'wineBScore' => 60],
                    ['name' => 'Acidity', 'wineAValue' => 'Medium', 'wineBValue' => 'High', 'wineAScore' => 60, 'wineBScore' => 85],
                    ['name' => 'Fruit Intensity', 'wineAValue' => 'Intense', 'wineBValue' => 'Moderate', 'wineAScore' => 88, 'wineBScore' => 70],
                    ['name' => 'Complexity', 'wineAValue' => 'Very High', 'wineBValue' => 'High', 'wineAScore' => 92, 'wineBScore' => 78],
                    ['name' => 'Finish Length', 'wineAValue' => 'Very Long', 'wineBValue' => 'Long', 'wineAScore' => 90, 'wineBScore' => 75],
                    ['name' => 'Oak Influence', 'wineAValue' => 'Prominent', 'wineBValue' => 'Moderate', 'wineAScore' => 80, 'wineBScore' => 55],
                    ['name' => 'Aging Potential', 'wineAValue' => '10+ years', 'wineBValue' => '5-10 years', 'wineAScore' => 95, 'wineBScore' => 70],
                ],
            ],
        ];
    }
}

