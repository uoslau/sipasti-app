<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
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
                'wilayah_tugas'         => 'required',
                'beban_kerja'           => 'required|integer',
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

        $validatedData = $validator->validated();

        // Cek apakah mitra sudah ada di kegiatan ini untuk menghindari duplikasi
        $cek_mitra = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $mitra->nik)
            ->exists();

        if ($cek_mitra) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', ucwords(strtolower($mitra->nama_mitra)) . ' sudah terdaftar di kegiatan ini!');
        }

        // Hitung honor berdasarkan wilayah tugas
        if ($validatedData['wilayah_tugas'] == "1201") {
            $honor_kegiatan = $kegiatan['honor_nias'] * $validatedData['beban_kerja'];
        } else {
            $honor_kegiatan = $kegiatan['honor_nias_barat'] * $validatedData['beban_kerja'];
        }

        PetugasKegiatan::create([
            'nik'                  => $mitra->nik,
            'kegiatan_id'          => $kegiatan->id,
            'bertugas_sebagai'     => $validatedData['bertugas_sebagai'],
            'wilayah_tugas'        => $validatedData['wilayah_tugas'],
            'beban_kerja'          => $validatedData['beban_kerja'],
            'satuan_beban_kerja'   => $validatedData['satuan_beban_kerja'],
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
                'wilayah_tugas'         => 'required',
                'beban_kerja'           => 'required|integer',
                'satuan_beban_kerja'    => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
            ]
        );

        if ($validator->fails()) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Petugas gagal diedit! Periksa input data Anda.');
        }

        $validatedData = $validator->validated();

        if ($validatedData['wilayah_tugas'] == "1201") {
            $honor_kegiatan = $kegiatan['honor_nias'];
        } else {
            $honor_kegiatan = $kegiatan['honor_nias_barat'];
        }

        PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('nik', $petugasKegiatan->nik)
            ->update([
                'bertugas_sebagai'      => $validatedData['bertugas_sebagai'],
                'wilayah_tugas'         => $validatedData['wilayah_tugas'],
                'beban_kerja'           => $validatedData['beban_kerja'],
                'satuan_beban_kerja'    => $validatedData['satuan_beban_kerja'],
                'honor'                 => $validatedData['beban_kerja'] * $honor_kegiatan,
            ]);

        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', 'Petugas berhasil diedit!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan, PetugasKegiatan $petugasKegiatan)
    {
        $petugasKegiatan->delete();
        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', ucwords(strtolower($petugasKegiatan->mitra->nama_mitra)) . ' berhasil dihapus!');
    }
}
