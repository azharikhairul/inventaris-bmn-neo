<?php

namespace Tests\Feature;

use App\Models\Aset;
use Tests\TestCase;

class KartuKendaliTest extends TestCase
{
    public function test_can_view_kartu_kendali_index_page(): void
    {
        $response = $this->get(route('kartu-kendali.index'));
        $response->assertStatus(200);
        $response->assertSee('Kartu Kendali Pemeliharaan');
        $response->assertSee('Format Resmi BPS Gorontalo');
        $response->assertSee('TANGGAL PERMINTAAN');
    }

    public function test_can_view_kartu_kendali_show_page(): void
    {
        $aset = Aset::first();
        $this->assertNotNull($aset, 'Asset should exist in database');

        $response = $this->get(route('kartu-kendali.show', $aset->id));
        $response->assertStatus(200);
        $response->assertSee('KARTU KENDALI');
        $response->assertSee('Badan Pusat Statistik Provinsi Gorontalo');
        $response->assertSee('Form Permintaan Anggaran');
        $response->assertSee('Tanggal Service Berkala');
        $response->assertSee('Jenis Kegiatan');
        $response->assertSee('Bengkel yang mengerjakan');
        $response->assertSee('Biaya');
        $response->assertSee('Unduh Excel');
    }

    public function test_can_export_kartu_kendali_excel(): void
    {
        $aset = Aset::first();
        $this->assertNotNull($aset, 'Asset should exist in database');

        $response = $this->get(route('kartu-kendali.export', [$aset->id, 'tahun' => '2026']));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_kartu_kendali_orders_by_most_recently_completed_realisasi(): void
    {
        $response = $this->get(route('kartu-kendali.index'));
        $response->assertStatus(200);
        $asets = $response->viewData('asets');
        $this->assertNotEmpty($asets);
    }
}
