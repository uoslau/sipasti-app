<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Imports\PetugasImport;
use App\Models\PetugasKegiatan;
use App\Imports\PetugasImportUpdate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class PetugasKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function search(Request $request)
    {
        $search = $request->input('q');

        $mitra  = Mitra::where('nama_mitra', 'LIKE', '%' . $search . '%')->take(5)->get(['nik', 'nama_mitra']);

        return response()->json($mitra);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function import(Request $request)
    {
        $slug           = $request->input('slug');

        $validated_data = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $kegiatan_id    = Kegiatan::where('slug', $slug)->value('id');

        Excel::import(new PetugasImport($kegiatan_id), $validated_data['excel_file']);

        return redirect()->back();
    }

    public function import_update(Request $request)
    {
        $slug           = $request->input('slug');

        $validated_data = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $kegiatan_id    = Kegiatan::where('slug', $slug)->value('id');

        Excel::import(new PetugasImportUpdate($kegiatan_id), $validated_data['excel_file']);

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Kegiatan $kegiatan)
    {
        $nik        = $request['nik'];

        $mitra      = Mitra::where('nik', $nik)->firstOrFail();

        $validator  = Validator::make(
            $request->all(),
            [
                'bertugas_sebagai'      => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
                'wilayah_tugas'         => 'required|in:1201,1225',
                'beban_kerja'           => 'required|integer|min:1',
                'satuan_beban_kerja'    => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
            ],
            [
                'bertugas_sebagai.regex'    => 'Field Bertugas Sebagai hanya boleh berisi huruf!',
                'satuan_beban_kerja.regex'  => 'Field Satuan Beban Kerja hanya boleh berisi huruf!',
            ]
        );

        if ($validator->fails()) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Petugas gagal ditambahkan! Periksa input data Anda.');
        }

        $validated_data = $validator->validated();

        // Cek apakah mitra sudah ada di kegiatan ini untuk menghindari duplikasi
        $cek_mitra = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $nik)
            ->exists();
        if ($cek_mitra) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', ucwords(strtolower($mitra->nama_mitra)) . ' sudah terdaftar di kegiatan ini!');
        }

        if (!$request->has('bypass_ob_check')) {
            $bulan_kegiatan_baru = \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('m');
            $tahun_kegiatan_baru = \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('Y');

            $cek_kegiatan_ob = PetugasKegiatan::where('nik', $nik)
                ->whereHas('kegiatan', function ($query) use ($bulan_kegiatan_baru, $tahun_kegiatan_baru) {
                    $query->where('is_ob', true)
                        ->whereRaw('MONTH(tanggal_mulai) = ?', [$bulan_kegiatan_baru])
                        ->whereRaw('YEAR(tanggal_mulai) = ?', [$tahun_kegiatan_baru]);
                })
                ->exists();

            if ($cek_kegiatan_ob) {
                return to_route('kegiatan.edit', $kegiatan->slug)
                    ->with('warning', ucwords(strtolower($mitra->nama_mitra)) . ' sudah terdaftar di kegiatan O-B pada bulan ini.')
                    ->withInput();
            }
        }

        // Hitung honor berdasarkan wilayah tugas & cek apakah merupakan kegiatan O-B
        if ($kegiatan->is_ob) {
            $honor = $validated_data['wilayah_tugas'] == "1201"
                ? $kegiatan->honor_nias : $kegiatan->honor_nias_barat;
        } else {
            $honor = $validated_data['wilayah_tugas'] == "1201"
                ? $kegiatan['honor_nias'] * $validated_data['beban_kerja']
                : $kegiatan['honor_nias_barat'] * $validated_data['beban_kerja'];
        }

        PetugasKegiatan::create([
            'nik'                  => $mitra->nik,
            'kegiatan_id'          => $kegiatan->id,
            'bertugas_sebagai'     => $validated_data['bertugas_sebagai'],
            'wilayah_tugas'        => $validated_data['wilayah_tugas'],
            'beban_kerja'          => $validated_data['beban_kerja'],
            'satuan_beban_kerja'   => $validated_data['satuan_beban_kerja'],
            'honor'                => $honor,
        ]);

        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', 'Petugas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PetugasKegiatan $petugasKegiatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan, PetugasKegiatan $petugasKegiatan)
    {
        // mengambil petugas_kegiatan yang berelasi dengan kegiatan berdasarkan nik
        $petugas_kegiatan = $kegiatan->petugasKegiatan()
            ->where('nik', $petugasKegiatan->nik)
            ->with('mitra')
            ->firstOrFail();

        $wilayah_tugas = WilayahTugas::all();

        return view('petugas.edit', [
            'kegiatan'          => $kegiatan,
            'petugas_kegiatan'  => $petugas_kegiatan,
            'wilayah_tugas'     => $wilayah_tugas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan, PetugasKegiatan $petugasKegiatan)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'bertugas_sebagai'      => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
                'wilayah_tugas'         => 'required|in:1201,1225',
                'beban_kerja'           => 'required|integer|min:1',
                'satuan_beban_kerja'    => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
            ]
        );

        if ($validator->fails()) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Petugas gagal diedit! Periksa input data Anda.');
        }

        $validated_data = $validator->validated();

        // hitung honor berdasarkan wilayah tugas & cek apakah merupakan kegiatan O-B
        if ($kegiatan->is_ob) {
            $honor = $validated_data['wilayah_tugas'] == "1201"
                ? $kegiatan->honor_nias : $kegiatan->honor_nias_barat;
        } else {
            $honor = $validated_data['wilayah_tugas'] == "1201"
                ? $kegiatan['honor_nias'] * $validated_data['beban_kerja']
                : $kegiatan['honor_nias_barat'] * $validated_data['beban_kerja'];
        }

        PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $petugasKegiatan->nik)
            ->update([
                'bertugas_sebagai'      => $validated_data['bertugas_sebagai'],
                'wilayah_tugas'         => $validated_data['wilayah_tugas'],
                'beban_kerja'           => $validated_data['beban_kerja'],
                'satuan_beban_kerja'    => $validated_data['satuan_beban_kerja'],
                'honor'                 => $honor,
            ]);

        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', 'Petugas berhasil diedit!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan, PetugasKegiatan $petugasKegiatan)
    {
        $petugas_kegiatan = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $petugasKegiatan->nik)
            ->firstOrFail();

        $petugas_kegiatan->delete();

        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', ucwords(strtolower($petugas_kegiatan->mitra->nama_mitra)) . ' berhasil dihapus!');
    }
}
