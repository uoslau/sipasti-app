<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\TimKerja;
use Illuminate\Support\Str;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Validated;
use App\Http\Requests\StoreKegiatanRequest;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // mengambil data kegiatan dengan relasi petugas_kegiatan dan tim_kerja serta menghitung total honor petugas_kegiatan untuk setiap kegiatan
        $kegiatan = Kegiatan::with(['PetugasKegiatan', 'TimKerja'])
            ->select('nama_kegiatan', 'slug', 'tanggal_mulai', 'tanggal_selesai', 'tim_kerja_id')
            ->withSum('PetugasKegiatan', 'honor')
            ->orderBy('id', 'desc')
            ->paginate(12);

        $tim_kerja = TimKerja::all();

        return view('kegiatan.index', [
            'kegiatan'  => $kegiatan,
            'tim_kerja' => $tim_kerja,
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $tim_kerja = TimKerja::all();

        // mengambil nama petugas_kegiatan yang berelasi dengan kegiatan berdasarkan nik
        $petugas_kegiatan = PetugasKegiatan::join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->orderBy('mitras.nama_mitra', 'asc')
            ->select('petugas_kegiatans.*', 'mitras.nama_mitra')
            ->paginate(12);

        $slug = $kegiatan->slug;

        $wilayah_tugas = WilayahTugas::all();

        $updated_at = $kegiatan->updated_at ? $kegiatan->updated_at->format('d M Y H:i') : 'Belum ada update';

        return view('kegiatan.edit', [
            'kegiatan'          => $kegiatan,
            'petugas_kegiatan'  => $petugas_kegiatan,
            'wilayah_tugas'     => $wilayah_tugas,
            'tim_kerja'         => $tim_kerja,
            'updated_at'        => $updated_at,
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

        // Update honor berdasarkan wilayah tugas
        PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('wilayah_tugas', '1201')
            ->update([
                'honor' => DB::raw('beban_kerja * ' . ($validatedData['honor_nias'] ?? 0))
            ]);

        PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('wilayah_tugas', '1225')
            ->update([
                'honor' => DB::raw('beban_kerja * ' . ($validatedData['honor_nias_barat'] ?? 0))
            ]);

        $slug = $kegiatan->slug;

        return redirect('/kegiatan/' . $slug . '/edit-kegiatan')->with('success', 'Kegiatan berhasil diedit!');
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
