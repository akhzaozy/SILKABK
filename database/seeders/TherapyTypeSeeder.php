<?php

namespace Database\Seeders;

use App\Models\TherapyType;
use Illuminate\Database\Seeder;

class TherapyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'           => 'Fisioterapi',
                'description'    => 'Terapi untuk meningkatkan kemampuan fisik dan motorik anak',
                'price_per_hour' => 150000,
            ],
            [
                'name'           => 'Terapi Sensori Integrasi',
                'description'    => 'Terapi untuk meningkatkan pengolahan informasi sensorik anak',
                'price_per_hour' => 150000,
            ],
            [
                'name'           => 'Terapi Okupasi',
                'description'    => 'Terapi untuk meningkatkan kemandirian dan kemampuan aktivitas sehari-hari',
                'price_per_hour' => 150000,
            ],
            [
                'name'           => 'Terapi Wicara',
                'description'    => 'Terapi untuk meningkatkan kemampuan komunikasi dan bicara anak',
                'price_per_hour' => 150000,
            ],
            [
                'name'           => 'Terapi Perilaku (ABA)',
                'description'    => 'Terapi perilaku berbasis Applied Behavior Analysis untuk membentuk perilaku positif',
                'price_per_hour' => 150000,
            ],
            [
                'name'           => 'Konsultasi Dokter',
                'description'    => 'Pemeriksaan dan konsultasi medis oleh dokter spesialis anak',
                'price_per_hour' => 200000,
            ],
            [
                'name'           => 'Asesmen Psikologi',
                'description'    => 'Asesmen dan evaluasi perkembangan psikologi anak oleh psikolog',
                'price_per_hour' => 250000,
            ],
            [
                'name'           => 'Tes IQ / Psikometri',
                'description'    => 'Tes kecerdasan intelektual dan pengukuran psikometri anak',
                'price_per_hour' => 250000,
            ],
            [
                'name'           => 'Konseling Keluarga',
                'description'    => 'Konseling untuk orang tua dan keluarga dalam mendampingi anak berkebutuhan khusus',
                'price_per_hour' => 150000,
            ],
        ];

        foreach ($types as $type) {
            TherapyType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
