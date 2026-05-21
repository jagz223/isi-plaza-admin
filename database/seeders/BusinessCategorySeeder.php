<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'Tecnología y Electrónica', 'slug' => 'tecnologia-electronica', 'sort_order' => 1],
            ['name' => 'Ropa y Moda', 'slug' => 'ropa-moda', 'sort_order' => 2],
            ['name' => 'Alimentos y Bebidas', 'slug' => 'alimentos-bebidas', 'sort_order' => 3],
            ['name' => 'Belleza y Cuidado Personal', 'slug' => 'belleza-cuidado-personal', 'sort_order' => 4],
            ['name' => 'Hogar y Muebles', 'slug' => 'hogar-muebles', 'sort_order' => 5],
            ['name' => 'Juguetes y Entretenimiento', 'slug' => 'juguetes-entretenimiento', 'sort_order' => 6],
            ['name' => 'Deportes y Fitness', 'slug' => 'deportes-fitness', 'sort_order' => 7],
            ['name' => 'Joyería y Accesorios', 'slug' => 'joyeria-accesorios', 'sort_order' => 8],
            ['name' => 'Salud y Farmacia', 'slug' => 'salud-farmacia', 'sort_order' => 9],
            ['name' => 'Materiales y Construcción', 'slug' => 'materiales-construccion', 'sort_order' => 10],
        ];

        $now = now();

        foreach ($rows as $row) {
            DB::table('business_categories')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
