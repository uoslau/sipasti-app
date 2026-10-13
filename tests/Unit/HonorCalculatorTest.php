<?php

use App\Models\Kegiatan;
use App\Models\WilayahTugas;
use App\Services\HonorCalculator;

uses(Tests\TestCase::class);

function buatKegiatan(array $attributes = []): Kegiatan
{
    return new Kegiatan(array_merge([
        'is_ob' => false,
        'honor_nias' => 1000000,
        'honor_nias_barat' => 2000000,
    ], $attributes));
}

it('menghitung honor non-OB sebagai tarif wilayah dikali beban kerja', function () {
    $honor = (new HonorCalculator())->calculate(buatKegiatan(), 3, WilayahTugas::KODE_NIAS);

    expect($honor)->toBe(3000000);
});

it('membayar honor OB secara flat sesuai tarif wilayah', function () {
    $honor = (new HonorCalculator())->calculate(buatKegiatan(['is_ob' => true]), 5, WilayahTugas::KODE_NIAS);

    expect($honor)->toBe(1000000);
});

it('memakai tarif Nias Barat untuk wilayah 1225', function () {
    $calculator = new HonorCalculator();

    expect($calculator->calculate(buatKegiatan(), 2, '1225'))->toBe(4000000)
        ->and($calculator->calculate(buatKegiatan(['is_ob' => true]), 2, '1225'))->toBe(2000000);
});

it('mengembalikan nol untuk honor non-OB kosong dan null untuk OB kosong', function () {
    $calculator = new HonorCalculator();

    expect($calculator->calculate(buatKegiatan(['honor_nias' => null]), 4, WilayahTugas::KODE_NIAS))->toBe(0)
        ->and($calculator->calculate(buatKegiatan(['is_ob' => true, 'honor_nias' => null]), 4, WilayahTugas::KODE_NIAS))->toBeNull();
});

it('menentukan cap honor berdasarkan posisi mitra', function () {
    $calculator = new HonorCalculator();

    expect($calculator->cap('Mitra Pendataan', 3627000, 3355000))->toBe(3627000)
        ->and($calculator->cap('Mitra Pengolahan', 3627000, 3355000))->toBe(3355000)
        ->and($calculator->cap('Mitra (Pendataan dan Pengolahan)', 3627000, 3355000, true))->toBe(3355000)
        ->and($calculator->cap('Mitra (Pendataan dan Pengolahan)', 3627000, 3355000, false))->toBe(3627000)
        ->and($calculator->cap('Posisi Lain', 3627000, 3355000))->toBe(0);
});
