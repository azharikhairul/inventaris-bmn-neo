<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\Realisasi;
use App\Models\Satker;
use App\Models\Supply;
use App\Models\SupplyDistribusi;
use App\Models\User;
use Illuminate\Database\Seeder;

class SatkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satkers = [
            [
                'kode_satker' => '054013100636900000KD',
                'nama_satker' => 'BPS Provinsi Gorontalo',
                'singkatan' => 'BPS Prov. Gorontalo',
                'tingkat' => 'Provinsi',
                'kabupaten_kota' => 'Provinsi Gorontalo',
                'alamat' => 'Jl. Moh. Thayeb Gobel No. 235, Kota Gorontalo',
                'telepon' => '(0435) 824240',
                'email' => 'bps7500@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013200636901000KD',
                'nama_satker' => 'BPS Kabupaten Boalemo',
                'singkatan' => 'BPS Kab. Boalemo',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Boalemo',
                'alamat' => 'Kompleks Perkantoran Pemkab Boalemo, Tilamuta',
                'telepon' => '(0443) 211029',
                'email' => 'bps7501@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013300636902000KD',
                'nama_satker' => 'BPS Kabupaten Gorontalo',
                'singkatan' => 'BPS Kab. Gorontalo',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Gorontalo',
                'alamat' => 'Jl. Trans Sulawesi, Kayubulan, Limboto',
                'telepon' => '(0435) 881389',
                'email' => 'bps7502@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013400636903000KD',
                'nama_satker' => 'BPS Kabupaten Pohuwato',
                'singkatan' => 'BPS Kab. Pohuwato',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Pohuwato',
                'alamat' => 'Kompleks Perkantoran Blok Plan, Marisa',
                'telepon' => '(0443) 211874',
                'email' => 'bps7503@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013500636904000KD',
                'nama_satker' => 'BPS Kabupaten Bone Bolango',
                'singkatan' => 'BPS Kab. Bone Bolango',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Bone Bolango',
                'alamat' => 'Jl. Prof. Dr. Aloei Saboe, Suwawa',
                'telepon' => '(0435) 8527267',
                'email' => 'bps7504@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013600636905000KD',
                'nama_satker' => 'BPS Kabupaten Gorontalo Utara',
                'singkatan' => 'BPS Kab. Gorontalo Utara',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Gorontalo Utara',
                'alamat' => 'Kwandang, Gorontalo Utara',
                'telepon' => '(0435) 8591040',
                'email' => 'bps7505@bps.go.id',
                'is_active' => true,
            ],
            [
                'kode_satker' => '054013700636971000KD',
                'nama_satker' => 'BPS Kota Gorontalo',
                'singkatan' => 'BPS Kota Gorontalo',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kota Gorontalo',
                'alamat' => 'Jl. P. Kalengkongan No. 25, Kota Gorontalo',
                'telepon' => '(0435) 821360',
                'email' => 'bps7571@bps.go.id',
                'is_active' => true,
            ],
        ];

        foreach ($satkers as $data) {
            Satker::updateOrCreate(
                ['kode_satker' => $data['kode_satker']],
                $data
            );
        }

        $provinsi = Satker::where('kode_satker', '054013100636900000KD')->first();
        if ($provinsi) {
            // Assign existing users, permintaans, realisasis, supplies to primary satker
            User::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);
            Permintaan::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);
            Realisasi::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);
            Supply::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);
            SupplyDistribusi::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);

            // Update asets with null kode_satker to provincial default
            Aset::whereNull('kode_satker')->update([
                'kode_satker' => $provinsi->kode_satker,
                'nama_satker' => $provinsi->nama_satker,
            ]);
        }
    }
}
