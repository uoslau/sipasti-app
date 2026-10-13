<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Services\NomorKontrakService;
use Illuminate\Support\Facades\DB;

class NomorKontrakController extends Controller
{
    public function __construct(
        private readonly NomorKontrakService $nomorKontrak,
    ) {
    }

    public function generate(Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        if ($kegiatan->is_generated) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Nomor SPK dan BAST sudah pernah digenerate.');
        }

        try {
            $generated = DB::transaction(fn () => $this->nomorKontrak->generate($kegiatan));

            if (! $generated) {
                return to_route('kegiatan.edit', $kegiatan->slug)
                    ->with('error', 'Belum ada petugas di kegiatan ini!');
            }

            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('success', 'Nomor Kontrak dan BAST berhasil digenerate.');
        } catch (\Throwable $th) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }
}
