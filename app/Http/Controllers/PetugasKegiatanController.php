<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
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
    public function store(StorePetugasKegiatanRequest $request, $slug)
    {
        $nik = $request['nik'];
        $mitra = Mitra::where('nik', $nik)->first();
        $kegiatan = Kegiatan::where('slug', $slug)->first();
        $validated = $request->validated();
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
    public function edit(PetugasKegiatan $petugasKegiatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetugasKegiatanRequest $request, PetugasKegiatan $petugasKegiatan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PetugasKegiatan $petugasKegiatan)
    {
        //
    }
}
