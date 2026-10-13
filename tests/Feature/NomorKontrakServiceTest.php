<?php

use App\Models\Fungsi;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\NomorKontrak;
use App\Models\PetugasKegiatan;
use App\Models\TimKerja;
use App\Models\WilayahTugas;
use App\Services\NomorKontrakService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function siapkanWilayahNias(): WilayahTugas
{
    return WilayahTugas::query()->first() ?? WilayahTugas::forceCreate([
        'kode_wilayah' => '1201',
        'nama_wilayah' => 'Nias',
        'honor_pendataan' => 3627000,
        'honor_pengolahan' => 3355000,
    ]);
}

function siapkanKegiatan(string $nama, string $tanggal): Kegiatan
{
    $timKerja = TimKerja::forceCreate([
        'nama_tim_kerja' => 'Tim ' . $nama,
        'alias_tim_kerja' => 'T-' . $nama,
    ]);

    $fungsi = Fungsi::forceCreate(['fungsi' => 'Fungsi ' . $nama]);

    return Kegiatan::forceCreate([
        'nama_kegiatan' => $nama,
        'tanggal_mulai' => $tanggal,
        'tanggal_selesai' => $tanggal,
        'beban_anggaran' => 'APBN',
        'tim_kerja_id' => $timKerja->id,
        'fungsi_id' => $fungsi->id,
        'honor_nias' => 100000,
        'honor_nias_barat' => 200000,
    ]);
}

function siapkanPetugas(Kegiatan $kegiatan, string $nik): PetugasKegiatan
{
    if (! Mitra::where('nik', $nik)->exists()) {
        Mitra::forceCreate([
            'nik' => $nik,
            'nama_mitra' => 'Mitra ' . $nik,
            'posisi' => 'Mitra Pendataan',
            'email' => $nik . '@example.com',
            'wilayah_id' => siapkanWilayahNias()->id,
            'alamat' => 'Jalan',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => true,
            'pekerjaan' => 'Petani',
        ]);
    }

    return PetugasKegiatan::forceCreate([
        'nik' => $nik,
        'kegiatan_id' => $kegiatan->id,
        'bertugas_sebagai' => 'PCL',
        'beban_kerja' => 2,
        'satuan_beban_kerja' => 'Dokumen',
        'honor' => 200000,
    ]);
}

it('mengembalikan false dan tidak membuat nomor bila belum ada petugas', function () {
    $kegiatan = siapkanKegiatan('Kegiatan Kosong', '2025-03-05');

    $hasil = (new NomorKontrakService())->generate($kegiatan);

    expect($hasil)->toBeFalse()
        ->and($kegiatan->fresh()->is_generated)->toBeFalse()
        ->and(NomorKontrak::count())->toBe(0);
});

it('membuat nomor kontrak dan bast lalu menandai kegiatan ter-generate', function () {
    $kegiatan = siapkanKegiatan('Kegiatan A', '2025-03-05');
    siapkanPetugas($kegiatan, '111');
    siapkanPetugas($kegiatan, '222');

    $hasil = (new NomorKontrakService())->generate($kegiatan);

    expect($hasil)->toBeTrue()
        ->and($kegiatan->fresh()->is_generated)->toBeTrue()
        ->and(NomorKontrak::count())->toBe(2);

    expect(NomorKontrak::orderBy('id')->pluck('nomor_kontrak')->all())->toBe([1, 2])
        ->and(NomorKontrak::orderBy('id')->pluck('nomor_bast')->all())->toBe([1, 2]);
});

it('memakai ulang nomor kontrak yang sama untuk petugas di bulan yang sama', function () {
    $kegiatanA = siapkanKegiatan('Kegiatan A', '2025-03-05');
    siapkanPetugas($kegiatanA, '999');
    (new NomorKontrakService())->generate($kegiatanA);

    $kegiatanB = siapkanKegiatan('Kegiatan B', '2025-03-20');
    siapkanPetugas($kegiatanB, '999');
    (new NomorKontrakService())->generate($kegiatanB);

    expect(NomorKontrak::where('nik', '999')->pluck('nomor_kontrak')->all())->toBe([1, 1])
        ->and(NomorKontrak::where('nik', '999')->pluck('nomor_bast')->all())->toBe([1, 2]);
});
