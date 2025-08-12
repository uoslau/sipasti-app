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
use App\Http\Requests\StorePetugasKegiatanRequest;
use App\Http\Requests\UpdatePetugasKegiatanRequest;

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
        $mitra      = Mitra::where('nik', $nik)->first();
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

        // Hitung honor berdasarkan wilayah tugas
        if ($validated_data['wilayah_tugas'] == "1201") {
            $honor_kegiatan = $kegiatan['honor_nias'] * $validated_data['beban_kerja'];
        } else {
            $honor_kegiatan = $kegiatan['honor_nias_barat'] * $validated_data['beban_kerja'];
        }

        PetugasKegiatan::create([
            'nik'                  => $nik,
            'kegiatan_id'          => $kegiatan->id,
            'bertugas_sebagai'     => $validated_data['bertugas_sebagai'],
            'wilayah_tugas'        => $validated_data['wilayah_tugas'],
            'beban_kerja'          => $validated_data['beban_kerja'],
            'satuan_beban_kerja'   => $validated_data['satuan_beban_kerja'],
            'honor'                => $honor_kegiatan,
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
            ->first();

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

        if ($validated_data['wilayah_tugas'] == "1201") {
            $honor_kegiatan = $kegiatan['honor_nias'];
        } else {
            $honor_kegiatan = $kegiatan['honor_nias_barat'];
        }

        PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $petugasKegiatan->nik)
            ->update([
                'bertugas_sebagai'      => $validated_data['bertugas_sebagai'],
                'wilayah_tugas'         => $validated_data['wilayah_tugas'],
                'beban_kerja'           => $validated_data['beban_kerja'],
                'satuan_beban_kerja'    => $validated_data['satuan_beban_kerja'],
                'honor'                 => $validated_data['beban_kerja'] * $honor_kegiatan,
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
