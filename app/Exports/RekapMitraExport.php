<?php

namespace App\Exports;

use App\Models\Mitra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapMitraExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tahun;

    // Menangkap tahun yang dikirim dari controller
    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    // 1. Ambil data dari Database (mirip seperti di Controller, tapi tanpa paginate)
    public function collection()
    {
        return Mitra::orderBy('nama_mitra', 'asc') // Sesuaikan dengan nama kolommu
            ->with(['petugasKegiatan' => function ($query) {
                $query->whereHas('kegiatan', function ($kegiatanQuery) {
                    $kegiatanQuery->whereYear('tanggal_mulai', $this->tahun);
                })->with('kegiatan');
            }])
            ->get(); // Menggunakan get() agar semua data terekspor
    }

    // 2. Format baris Excel
    public function map($mitra): array
    {
        $penugasan = collect($mitra->petugasKegiatan ?? []);

        $kegiatanPerBulan = $penugasan->groupBy(function ($item) {
            $tanggal = $item->kegiatan->tanggal_mulai ?? null;
            return $tanggal ? \Carbon\Carbon::parse($tanggal)->format('n') : 0;
        })->map->count();

        // Menyusun satu baris data (Row)
        $baris = [
            $mitra->nama_mitra ?? $mitra->nama,
        ];

        // Memasukkan data bulan 1 sampai 12
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $baris[] = $kegiatanPerBulan->get($bulan, 0); // Jika 0 biarkan 0 di Excel
        }

        // Memasukkan Total
        $baris[] = $penugasan->count();

        return $baris;
    }

    // 3. Buat Judul Kolom (Header)
    public function headings(): array
    {
        return [
            'Nama Mitra',
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agt',
            'Sep',
            'Okt',
            'Nov',
            'Des',
            'Total'
        ];
    }

    // 4. Gaya (Styling) Excel
    public function styles(Worksheet $sheet)
    {
        return [
            // Bikin baris pertama (header) jadi Bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
