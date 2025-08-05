<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\TimKerja;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
use Illuminate\Auth\Events\Validated;
use App\Http\Requests\StoreKegiatanRequest;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('kegiatan.index', [
            'kegiatan' => Kegiatan::all(),
            'tim_kerja' => TimKerja::all(),
        ]);
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
    public function store(Request $request)
    {
        // mengembalikan value honor yang sudah diformat dengan menghilangkan . sebagai pemisah ribuan, juta, dst.
        $request->merge([
            'honor_nias' => $request->honor_nias ? str_replace('.', '', $request->honor_nias) : null,
            'honor_nias_barat' => $request->honor_nias_barat ? str_replace('.', '', $request->honor_nias_barat) : null,
        ]);

        $validatedData = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date',
            'beban_anggaran'    => 'required|string|max:255',
            'tim_kerja_id'      => 'required',
            'honor_nias'        => 'nullable|integer',
            'honor_nias_barat'  => 'nullable|integer',
        ]);

        Kegiatan::create($validatedData);

        return redirect('/kegiatan')->with('success', 'Kegiatan berhasil ditambahkan!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        $petugas_kegiatan = PetugasKegiatan::join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->orderBy('mitras.nama_mitra', 'desc')
            ->select('petugas_kegiatans.*', 'mitras.nama_mitra')
            ->paginate(12);

        $slug = $kegiatan->slug;

        return view('kegiatan.show', [
            'nama_kegiatan'     => $kegiatan->nama_kegiatan,
            'slug'              => $slug,
            'petugas_kegiatan'  => $petugas_kegiatan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $tim_kerja = TimKerja::all();
        return view('kegiatan.edit', [
            'kegiatan'      => $kegiatan,
            'tim_kerja'     => $tim_kerja,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        // mengembalikan value honor yang sudah diformat dengan menghilangkan . sebagai pemisah ribuan, juta, dst.
        $request->merge([
            'honor_nias' => $request->honor_nias ? str_replace('.', '', $request->honor_nias) : null,
            'honor_nias_barat' => $request->honor_nias_barat ? str_replace('.', '', $request->honor_nias_barat) : null,
        ]);

        $validatedData = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date',
            'beban_anggaran'    => 'required|string|max:255',
            'tim_kerja_id'      => 'required',
            'honor_nias'        => 'nullable|integer',
            'honor_nias_barat'  => 'nullable|integer',
        ]);

        Kegiatan::where('id', $kegiatan->id)
            ->update($validatedData);

        return redirect('/kegiatan')->with('success', 'Kegiatan berhasil diedit!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus!');
    }
}
