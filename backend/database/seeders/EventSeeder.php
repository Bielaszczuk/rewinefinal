<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'partner@rewine.local')->first();
        if (!$organizer) {
            return;
        }

        $events = [
            [
                'id'               => 'c1d2e3f4-0001-0001-0001-200000000001',
                'title'            => 'Gran Degustación Malbec Mendoza 2026',
                'description'      => 'Degustación de los mejores Malbec de la temporada con más de 30 bodegas participantes.',
                'type'             => 'TASTING',
                'start_date'       => now()->addDays(30),
                'end_date'         => now()->addDays(30)->addHours(6),
                'location_name'    => 'Centro de Convenciones Mendoza',
                'location_address' => 'Av. San Martín 1143',
                'location_city'    => 'Mendoza',
                'location_region'  => 'Mendoza',
                'latitude'         => -32.8908,
                'longitude'        => -68.8272,
                'price'            => 25.00,
                'max_attendees'    => 200,
                'current_attendees' => 45,
                'status'           => 'PUBLISHED',
                'image_url'        => null,
                'organizer_id'     => $organizer->id,
                'organizer_type'   => 'PARTNER',
                'contact_email'    => 'info@vinosmendoza.com',
                'contact_phone'    => '+54 261 555-1234',
                'website_url'      => 'https://www.vinosmendoza.com',
            ],
            [
                'id'               => 'c1d2e3f4-0002-0002-0002-200000000002',
                'title'            => 'Vendimia Festival Valle de Uco',
                'description'      => 'Celebración de la cosecha en el Valle de Uco con visitas a viñedos y catas exclusivas.',
                'type'             => 'FESTIVAL',
                'start_date'       => now()->addDays(45),
                'end_date'         => now()->addDays(47),
                'location_name'    => 'Bodega Zuccardi Valle de Uco',
                'location_address' => 'Ruta Provincial 89',
                'location_city'    => 'Tunuyán',
                'location_region'  => 'Mendoza',
                'latitude'         => -33.5737,
                'longitude'        => -69.0183,
                'price'            => 80.00,
                'max_attendees'    => 100,
                'current_attendees' => 32,
                'status'           => 'PUBLISHED',
                'image_url'        => null,
                'organizer_id'     => $organizer->id,
                'organizer_type'   => 'WINERY',
                'contact_email'    => 'eventos@familiazuccardi.com',
                'contact_phone'    => '+54 261 441-0000',
                'website_url'      => 'https://www.familiazuccardi.com',
            ],
            [
                'id'               => 'c1d2e3f4-0003-0003-0003-200000000003',
                'title'            => 'Tour de Bodegas Luján de Cuyo',
                'description'      => 'Recorrido guiado por las principales bodegas de Luján de Cuyo con almuerzo incluido.',
                'type'             => 'TOUR',
                'start_date'       => now()->addDays(15),
                'end_date'         => now()->addDays(15)->addHours(8),
                'location_name'    => 'Punto de encuentro: Bodega Catena',
                'location_address' => 'Cobos s/n',
                'location_city'    => 'Luján de Cuyo',
                'location_region'  => 'Mendoza',
                'latitude'         => -33.0472,
                'longitude'        => -68.8728,
                'price'            => 120.00,
                'max_attendees'    => 20,
                'current_attendees' => 12,
                'status'           => 'PUBLISHED',
                'image_url'        => null,
                'organizer_id'     => $organizer->id,
                'organizer_type'   => 'PARTNER',
                'contact_email'    => 'tours@rewine.com',
                'contact_phone'    => '+54 261 555-9999',
                'website_url'      => null,
            ],
            [
                'id'               => 'c1d2e3f4-0004-0004-0004-200000000004',
                'title'            => 'Clase de Cata: Introducción al Vino',
                'description'      => 'Clase introductoria para aprender los fundamentos de la cata profesional.',
                'type'             => 'CLASS',
                'start_date'       => now()->addDays(7),
                'end_date'         => now()->addDays(7)->addHours(3),
                'location_name'    => 'Escuela del Vino Mendoza',
                'location_address' => 'Chile 898',
                'location_city'    => 'Mendoza',
                'location_region'  => 'Mendoza',
                'latitude'         => -32.8895,
                'longitude'        => -68.8458,
                'price'            => 35.00,
                'max_attendees'    => 15,
                'current_attendees' => 8,
                'status'           => 'PUBLISHED',
                'image_url'        => null,
                'organizer_id'     => $organizer->id,
                'organizer_type'   => 'INDIVIDUAL',
                'contact_email'    => 'escuela@vino.com',
                'contact_phone'    => '+54 261 555-4321',
                'website_url'      => null,
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(['id' => $event['id']], $event);
        }
    }
}
