<?php

namespace Database\Seeders;

use App\Models\Treatment;
use App\Models\TreatmentSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TreatmentSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name' => 'Ortodoncia',
                'slug' => 'ortodoncia',
                'sort_order' => 1,
                'treatments' => [
                    'Brackets metálicos',
                    'Brackets estéticos',
                    'Alineadores',
                    'Retenedores',
                ],
            ],
            [
                'name' => 'Estética',
                'slug' => 'estetica',
                'sort_order' => 2,
                'treatments' => [
                    'Blanqueamiento',
                    'Carillas',
                    'Resina estética',
                ],
            ],
            [
                'name' => 'Implantes',
                'slug' => 'implantes',
                'sort_order' => 3,
                'treatments' => [
                    'Implante unitario',
                    'Prótesis sobre implante',
                ],
            ],
            [
                'name' => 'Endodoncia',
                'slug' => 'endodoncia',
                'sort_order' => 4,
                'treatments' => [
                    'Tratamiento de conducto',
                    'Retratamiento endodóntico',
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $section = TreatmentSection::query()->updateOrCreate(
                ['slug' => $sectionData['slug']],
                [
                    'name' => $sectionData['name'],
                    'sort_order' => $sectionData['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach ($sectionData['treatments'] as $index => $treatmentName) {
                $slug = Str::slug($treatmentName);
                Treatment::query()->updateOrCreate(
                    [
                        'treatment_section_id' => $section->id,
                        'slug' => $slug,
                    ],
                    [
                        'name' => $treatmentName,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
