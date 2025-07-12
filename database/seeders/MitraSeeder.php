<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MitraSeeder extends Seeder
{
    public function run(): void
    {
        $mitra = [
            'Biaya_BANK',
            'Bina_Tehnik-Renovasi_Puskes',
            'Jasa_Giro',
            'Operasional_Klinik',
            'Medigo_Teknologi',
            'Biaya_Ambulan',
            'DRG_Rita_Kustari',
            'DRG_Nurlita',
            'PBF_Parit Padang',
            'PBF_Mensa Binasukses',
            'PBF_Tri Sapta Jaya',
            'PBF_Anggun',
            'PBF_Kimia-Farma',
            'PBF_Alfa-Qinan',
            'PBF_Millennium',
            'PBF_United Dico',
            'PBF_Elkaka',
            'PBF_Sapta-Sari-Tama',
            'Optik_Paten',
            'Apotek_Enggal',
            'Apotek_Purna_Husada',
            'Kimia_Farma',
            'Prodia',
            'PMK_Rumah Sakit',
            'PMK_Optik',
            'PMK_Dokter-Gigi',
            'PMK_Laboratorium',
            'PMK_Apotek',
            'PMK_PBF',
            'Hermina',
            'Advent',
            'Bumi_Waras',
            'Urip_Sumoharjo',
            'Airan_Raya',
            'Bintang_Amin',
            '(DKT)_TK-IV-020704',
            'Natar_Medika'
        ];

        foreach ($mitra as $nama) {
            DB::table('mitras')->updateOrInsert(
                ['nama' => $nama],
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }
    }
}
