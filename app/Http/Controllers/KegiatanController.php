<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\TimKerja;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // mengambil data kegiatan dengan relasi petugas_kegiatan dan tim_kerja serta menghitung total honor petugas_kegiatan untuk setiap kegiatan
        $kegiatan = Kegiatan::with(['petugasKegiatan', 'timKerja'])
            ->select('nama_kegiatan', 'slug', 'tanggal_mulai', 'tanggal_selesai', 'tim_kerja_id')
            ->withSum('petugasKegiatan', 'honor')
            ->orderBy('id', 'desc')
            ->paginate(15);

        // mengambil data tim kerja untuk create kegiatan
        $tim_kerja = TimKerja::select('id', 'nama_tim_kerja', 'alias_tim_kerja')->get();

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
            'honor_nias' => parseNominal($request->honor_nias),
            'honor_nias_barat' => parseNominal($request->honor_nias_barat),
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

        return to_route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil ditambahkan!');
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
        // mengambil data tim kerja untuk create kegiatan
        $tim_kerja = TimKerja::select('id', 'nama_tim_kerja', 'alias_tim_kerja')->get();

        // mengambil nama petugas_kegiatan yang berelasi dengan kegiatan berdasarkan nik
        $petugas_kegiatan = PetugasKegiatan::join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->orderBy('mitras.nama_mitra', 'asc')
            ->select('petugas_kegiatans.*', 'mitras.nama_mitra')
            ->paginate(15);

        $wilayah_tugas = WilayahTugas::all();

        $kegiatan_updated_at = $kegiatan->created_at != $kegiatan->updated_at
            ? '- [Terakhir Diupdate: ' . $kegiatan->updated_at->format('d M Y H:i') . ']'
            : '';

        $petugas_created_at = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->orderBy('created_at', 'desc')
            ->value('created_at');
        $petugas_updated_at = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->orderBy('updated_at', 'desc')
            ->value('updated_at');
        $petugas_kegiatan_updated_at = $petugas_created_at != $petugas_updated_at
            ? '- [Terakhir Diupdate: ' . $petugas_updated_at->format('d M Y H:i') . ']'
            : '';

        return view('kegiatan.edit', [
            'kegiatan'                      => $kegiatan,
            'petugas_kegiatan'              => $petugas_kegiatan,
            'wilayah_tugas'                 => $wilayah_tugas,
            'tim_kerja'                     => $tim_kerja,
            'kegiatan_updated_at'           => $kegiatan_updated_at,
            'petugas_kegiatan_updated_at'   => $petugas_kegiatan_updated_at,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        // mengembalikan value honor yang sudah diformat dengan menghilangkan . sebagai pemisah ribuan, juta, dst.
        $request->merge([
            'honor_nias' => parseNominal($request->honor_nias),
            'honor_nias_barat' => parseNominal($request->honor_nias_barat),
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

        return to_route('kegiatan.edit', $kegiatan->slug)
            ->with('success', 'Kegiatan berhasil diedit!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

        return to_route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus!');
    }
}
