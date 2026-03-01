<?php

namespace Database\Seeders;

use App\Models\Winery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WinerySeeder extends Seeder
{
    public function run(): void
    {
        $wineries = [
            [
                'id'          => 'a1b2c3d4-0001-0001-0001-000000000001',
                'name'        => 'Bodega Catena Zapata',
                'country'     => 'Argentina',
                'region'      => 'Mendoza',
                'subregion'   => 'Luján de Cuyo',
                'description' => 'Una de las bodegas más reconocidas de Argentina, pionera en la producción de Malbec de altura.',
                'website_url' => 'https://www.catenazapata.com',
                'logo_url'    => null,
                'established' => 1902,
            ],
            [
                'id'          => 'a1b2c3d4-0002-0002-0002-000000000002',
                'name'        => 'Luigi Bosca',
                'country'     => 'Argentina',
                'region'      => 'Mendoza',
                'subregion'   => 'Luján de Cuyo',
                'description' => 'Bodega familiar con más de 120 años de historia en la elaboración de vinos premium.',
                'website_url' => 'https://www.luigibosca.com.ar',
                'logo_url'    => null,
                'established' => 1901,
            ],
            [
                'id'          => 'a1b2c3d4-0003-0003-0003-000000000003',
                'name'        => 'Zuccardi Valle de Uco',
                'country'     => 'Argentina',
                'region'      => 'Mendoza',
                'subregion'   => 'Valle de Uco',
                'description' => 'Bodega de vanguardia en Valle de Uco, reconocida como mejor bodega del mundo en 2019.',
                'website_url' => 'https://www.familiazuccardi.com',
                'logo_url'    => null,
                'established' => 1963,
            ],
            [
                'id'          => 'a1b2c3d4-0004-0004-0004-000000000004',
                'name'        => 'Achaval Ferrer',
                'country'     => 'Argentina',
                'region'      => 'Mendoza',
                'subregion'   => 'Luján de Cuyo',
                'description' => 'Boutique winery especializada en Malbec de viñedos viejos.',
                'website_url' => 'https://www.achaval-ferrer.com',
                'logo_url'    => null,
                'established' => 1998,
            ],
            [
                'id'          => 'a1b2c3d4-0005-0005-0005-000000000005',
                'name'        => 'Clos de los Siete',
                'country'     => 'Argentina',
                'region'      => 'Mendoza',
                'subregion'   => 'Valle de Uco',
                'description' => 'Proyecto vitivinícola francés en el corazón del Valle de Uco.',
                'website_url' => 'https://www.closdelossiete.com',
                'logo_url'    => null,
                'established' => 2002,
            ],
            [
                'id'          => 'a1b2c3d4-0006-0006-0006-000000000006',
                'name'        => 'Concha y Toro',
                'country'     => 'Chile',
                'region'      => 'Valle del Maipo',
                'subregion'   => 'Puente Alto',
                'description' => 'La mayor viña chilena y una de las más importantes del mundo.',
                'website_url' => 'https://www.conchaytoro.com',
                'logo_url'    => null,
                'established' => 1883,
            ],
            [
                'id'          => 'a1b2c3d4-0007-0007-0007-000000000007',
                'name'        => 'Casa Lapostolle',
                'country'     => 'Chile',
                'region'      => 'Valle de Colchagua',
                'subregion'   => null,
                'description' => 'Bodega de origen francés con viñedos en los mejores terroirs de Chile.',
                'website_url' => 'https://www.lapostolle.com',
                'logo_url'    => null,
                'established' => 1994,
            ],
        ];

        foreach ($wineries as $winery) {
            Winery::firstOrCreate(['id' => $winery['id']], $winery);
        }
    }
}
