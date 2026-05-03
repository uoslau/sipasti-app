<?php

namespace App\Http\Controllers;

use App\Exports\RekapMitraExport;
use App\Http\Requests\StoreMitraRequest;
use App\Http\Requests\UpdateMitraRequest;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class MitraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil filter tahun dari request, default ke tahun saat ini
        $tahun = $request->input('tahun', date('Y'));

        // Ambil data mitra
        $mitras = Mitra::orderBy('nama_mitra', 'asc')
            ->with(['petugasKegiatan' => function ($query) use ($tahun) {
                // 1. Filter petugasKegiatan HANYA yang kegiatan-nya terjadi di tahun yang dipilih
                $query->whereHas('kegiatan', function ($kegiatanQuery) use ($tahun) {
                    $kegiatanQuery->whereYear('tanggal_mulai', $tahun);
                })->with('kegiatan'); // 2. Muat (load) relasi kegiatan agar kita bisa membaca tanggalnya di bawah

            }])->paginate(20) // 1. UBAH get() MENJADI paginate(10)
            ->withQueryString() // 2. TAMBAHKAN INI agar filter tahun tetap terbawa di URL halaman 2, 3, dst
            ->through(function ($mitra) { // 3. UBAH map() MENJADI through()

                // Gunakan collect() untuk berjaga-jaga
                $penugasan = collect($mitra->petugasKegiatan ?? []);

                // Kelompokkan data penugasan berdasarkan bulan (1 = Januari, 12 = Desember)
                $kegiatanPerBulan = $penugasan->groupBy(function ($item) {
                    // 3. PERBAIKAN UTAMA: Ambil tanggal dari tabel kegiatan, bukan dari pivot
                    $tanggal = $item->kegiatan->tanggal_mulai ?? null;

                    // Jika tanggalnya ada, ambil bulannya. Jika null, kembalikan 0 (abaikan).
                    if ($tanggal) {
                        return Carbon::parse($tanggal)->format('n');
                    }
                    return 0;
                })->map->count();

                // Siapkan array data 12 bulan (isi 0 jika tidak ada kegiatan)
                $rekapBulanan = [];
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $rekapBulanan[$bulan] = $kegiatanPerBulan->get($bulan, 0);
                }

                return [
                    'nama'              => $mitra->nama_mitra,
                    'rekap_bulanan'     => $rekapBulanan,
                    'total_kegiatan'    => $penugasan->count(),
                ];
            });


        // Lempar data ke view Blade
        return view('mitra.index', compact('mitras', 'tahun'));
    }

    // Kembali gunakan kode yang rapi ini di RekapMitraController:
    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $namaFile = 'Rekap_Mitra_Tahun_' . $tahun . '.xlsx';

        return Excel::download(new RekapMitraExport($tahun), $namaFile);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMitraRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Mitra $mitra)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mitra $mitra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMitraRequest $request, Mitra $mitra)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mitra $mitra)
    {
        //
    }
}
