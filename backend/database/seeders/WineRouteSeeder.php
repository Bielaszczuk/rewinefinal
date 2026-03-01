<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Winery;
use App\Models\WineRoute;
use App\Models\WineRouteStop;
use Illuminate\Database\Seeder;

class WineRouteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@rewine.local')->first();
        if (!$admin) {
            return;
        }

        $routes = [
            [
                'id'                   => 'd1e2f3a4-0001-0001-0001-300000000001',
                'name'                 => 'Ruta del Malbec – Luján de Cuyo',
                'description'          => 'Recorrido por las bodegas más emblemáticas de Luján de Cuyo, cuna del Malbec argentino.',
                'country'              => 'Argentina',
                'region'               => 'Mendoza',
                'subregion'            => 'Luján de Cuyo',
                'estimated_duration'   => 480,
                'estimated_days'       => 1,
                'total_distance'       => 45.0,
                'difficulty'           => 'easy',
                'image_url'            => null,
                'status'               => 'active',
                'recommended_wine_types' => ['RED'],
                'created_by'           => $admin->id,
                'wineries'             => ['a1b2c3d4-0001-0001-0001-000000000001', 'a1b2c3d4-0002-0002-0002-000000000002', 'a1b2c3d4-0004-0004-0004-000000000004'],
                'stops'                => [
                    ['name' => 'Bodega Catena Zapata', 'type' => 'winery', 'address' => 'Cobos s/n, Agrelo', 'latitude' => -33.0472, 'longitude' => -68.8728, 'stop_order' => 1, 'estimated_duration' => 90],
                    ['name' => 'Luigi Bosca Bodega', 'type' => 'winery', 'address' => 'San Martín 2044, Mayor Drummond', 'latitude' => -33.0200, 'longitude' => -68.8700, 'stop_order' => 2, 'estimated_duration' => 90],
                    ['name' => 'Mirador Los Andes', 'type' => 'viewpoint', 'address' => 'Ruta Provincial 15', 'latitude' => -33.0800, 'longitude' => -68.8900, 'stop_order' => 3, 'estimated_duration' => 30],
                    ['name' => 'Achaval Ferrer', 'type' => 'winery', 'address' => 'Cobos 2601, Perdriel', 'latitude' => -33.0600, 'longitude' => -68.8850, 'stop_order' => 4, 'estimated_duration' => 90],
                ],
            ],
            [
                'id'                   => 'd1e2f3a4-0002-0002-0002-300000000002',
                'name'                 => 'Valle de Uco Expedition',
                'description'          => 'Exploración del Valle de Uco, tierra de vinos de altura y paisajes montañosos únicos.',
                'country'              => 'Argentina',
                'region'               => 'Mendoza',
                'subregion'            => 'Valle de Uco',
                'estimated_duration'   => 720,
                'estimated_days'       => 2,
                'total_distance'       => 120.0,
                'difficulty'           => 'moderate',
                'image_url'            => null,
                'status'               => 'active',
                'recommended_wine_types' => ['RED', 'WHITE'],
                'created_by'           => $admin->id,
                'wineries'             => ['a1b2c3d4-0003-0003-0003-000000000003', 'a1b2c3d4-0005-0005-0005-000000000005'],
                'stops'                => [
                    ['name' => 'Zuccardi Valle de Uco', 'type' => 'winery', 'address' => 'Ruta Provincial 89, Los Árboles', 'latitude' => -33.5737, 'longitude' => -69.0183, 'stop_order' => 1, 'estimated_duration' => 120],
                    ['name' => 'Clos de los Siete', 'type' => 'winery', 'address' => 'Manzano Histórico s/n, Tunuyán', 'latitude' => -33.5500, 'longitude' => -69.0500, 'stop_order' => 2, 'estimated_duration' => 120],
                    ['name' => 'Mirador Cordillera', 'type' => 'viewpoint', 'address' => 'Ruta Provincial 89 km 40', 'latitude' => -33.6000, 'longitude' => -69.1000, 'stop_order' => 3, 'estimated_duration' => 60],
                ],
            ],
            [
                'id'                   => 'd1e2f3a4-0003-0003-0003-300000000003',
                'name'                 => 'Colchagua Wine Tour',
                'description'          => 'Recorrido por el Valle de Colchagua, hogar de los mejores vinos de Chile.',
                'country'              => 'Chile',
                'region'               => 'Valle de Colchagua',
                'subregion'            => null,
                'estimated_duration'   => 600,
                'estimated_days'       => 2,
                'total_distance'       => 85.0,
                'difficulty'           => 'easy',
                'image_url'            => null,
                'status'               => 'active',
                'recommended_wine_types' => ['RED'],
                'created_by'           => $admin->id,
                'wineries'             => ['a1b2c3d4-0007-0007-0007-000000000007'],
                'stops'                => [
                    ['name' => 'Casa Lapostolle – Clos Apalta', 'type' => 'winery', 'address' => 'Clos Apalta, Santa Cruz', 'latitude' => -34.6500, 'longitude' => -71.3500, 'stop_order' => 1, 'estimated_duration' => 150],
                    ['name' => 'Santa Cruz Wine Museum', 'type' => 'museum', 'address' => 'Plaza de Armas, Santa Cruz', 'latitude' => -34.6382, 'longitude' => -71.3615, 'stop_order' => 2, 'estimated_duration' => 90],
                ],
            ],
        ];

        foreach ($routes as $routeData) {
            $wineryIds = $routeData['wineries'] ?? [];
            $stops     = $routeData['stops'] ?? [];
            unset($routeData['wineries'], $routeData['stops']);

            $route = WineRoute::firstOrCreate(['id' => $routeData['id']], $routeData);

            // Attach wineries
            if (!empty($wineryIds)) {
                $route->wineries()->syncWithoutDetaching(
                    Winery::whereIn('id', $wineryIds)->pluck('id')->toArray()
                );
            }

            // Create stops
            foreach ($stops as $stopData) {
                $stopData['id'] = \Illuminate\Support\Str::uuid()->toString();
                $stopData['wine_route_id'] = $route->id;
                if (!WineRouteStop::where('wine_route_id', $route->id)->where('stop_order', $stopData['stop_order'])->exists()) {
                    WineRouteStop::create($stopData);
                }
            }
        }
    }
}
