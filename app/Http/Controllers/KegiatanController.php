<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Models\TimKerja;
use App\Models\NomorKontrak;
use App\Models\WilayahTugas;
use Illuminate\Http\Request;
use App\Helpers\NumberToWords;
use App\Models\PetugasKegiatan;
use App\Services\HonorCalculator;
use App\Services\NomorKontrakService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Traits\GeneratesBastDocuments;

class KegiatanController extends Controller
{
    use GeneratesBastDocuments;

    public function __construct(
        private readonly HonorCalculator $honor,
        private readonly NomorKontrakService $nomorKontrak,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $user = Auth::user();

        // mengambil data kegiatan dengan relasi petugas_kegiatan dan tim_kerja serta menghitung total honor petugas_kegiatan untuk setiap kegiatan
        $query = Kegiatan::with(['petugasKegiatan', 'fungsi', 'timKerja'])
            ->select('nama_kegiatan', 'is_generated', 'slug', 'tanggal_mulai', 'tanggal_selesai', 'fungsi_id', 'tim_kerja_id')
            ->withSum('petugasKegiatan', 'honor')
            ->orderBy('id', 'desc');

        // pencarian dikelompokkan agar kondisi OR tidak mengganggu kondisi query lainnya
        $query->when($search, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('timKerja', function ($timQuery) use ($search) {
                        $timQuery->where('alias_tim_kerja', 'like', "%{$search}%");
                    });
            });
        });

        $kegiatan = $query->paginate(10);

        $kegiatan->appends(['search' => $search]);

        // admin dapat memilih semua tim kerja; user hanya tim kerja yang diikuti
        $tim_kerja = $user->isAdmin()
            ? TimKerja::all()
            : TimKerja::whereIn('id', $user->timKerjaIds())->get();

        $userTimKerja = $user->timKerja->first();

        $user_tim_kerja_id = $userTimKerja ? $userTimKerja->id : null;

        return view('kegiatan.index', [
            'kegiatan'          => $kegiatan,
            'tim_kerja'         => $tim_kerja,
            'search'            => $search,
            'user_tim_kerja_id' => $user_tim_kerja_id,
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
        $this->authorize('create', Kegiatan::class);

        // mengembalikan value honor yang sudah diformat dengan menghilangkan . sebagai pemisah ribuan, juta, dst.
        // dd($request);
        $request->merge([
            'honor_nias' => parseNominal($request->honor_nias),
        ]);

        // validasi inputan form
        $validated_data = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255|not_regex:/[\/\\\]/',
            'is_ob'             => 'nullable|boolean',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
                function ($attribute, $value, $fail) use ($request) {
                    $tanggal_mulai      = Carbon::parse($request->tanggal_mulai);
                    $tanggal_selesai    = Carbon::parse($value);

                    if ($tanggal_mulai->month !== $tanggal_selesai->month || $tanggal_mulai->year !== $tanggal_selesai->year) {
                        $fail('Tanggal mulai dan tanggal selesai harus dalam bulan yang sama.');
                    }
                },
            ],
            'beban_anggaran'    => 'required|string|max:255',
            'tim_kerja_id'      => 'required|exists:tim_kerjas,id',
            'honor_nias'        => 'nullable|integer',
        ], [
            'nama_kegiatan.not_regex' => 'Nama kegiatan tidak boleh mengandung karakter garis miring!',
        ]);

        // user non-admin hanya boleh menambah kegiatan pada tim kerjanya
        if (! Auth::user()->isAdmin() && ! in_array((int) $validated_data['tim_kerja_id'], Auth::user()->timKerjaIds())) {
            abort(403, 'Anda tidak berhak menambah kegiatan pada tim kerja ini.');
        }

        Kegiatan::create($validated_data);

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
        $this->authorize('view', $kegiatan);

        // admin dapat memilih semua tim kerja; user hanya tim kerja yang diikuti
        $user = Auth::user();
        $tim_kerja = TimKerja::select('id', 'nama_tim_kerja', 'alias_tim_kerja')
            ->when(! $user->isAdmin(), fn ($q) => $q->whereIn('id', $user->timKerjaIds()))
            ->get();

        // mengambil nama petugas_kegiatan yang berelasi dengan kegiatan berdasarkan nik
        $petugas_kegiatan = PetugasKegiatan::join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('wilayah_tugas', 'wilayah_tugas.id', '=', 'mitras.wilayah_id')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->orderBy('mitras.nama_mitra', 'asc')
            ->select('petugas_kegiatans.*', 'mitras.nama_mitra', 'wilayah_tugas.nama_wilayah')
            ->paginate(10);

        $is_empty_petugas = $petugas_kegiatan->isEmpty();

        // menambahkan keterangan waktu update kegiatan dan petugas_kegiatan
        $kegiatan_updated_at = $kegiatan->created_at != $kegiatan->updated_at
            ? '*terakhir diubah: ' . $kegiatan->updated_at->format('d M Y H:i')
            : '';

        $petugas_created_at = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->orderBy('created_at', 'desc')
            ->value('created_at');
        $petugas_updated_at = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
            ->orderBy('updated_at', 'desc')
            ->value('updated_at');
        $petugas_kegiatan_updated_at = $petugas_created_at != $petugas_updated_at
            ? '*terakhir diubah: ' . $petugas_updated_at->format('d M Y H:i')
            : '';

        return view('kegiatan.edit', [
            'kegiatan'                      => $kegiatan,
            'petugas_kegiatan'              => $petugas_kegiatan,
            'tim_kerja'                     => $tim_kerja,
            'is_empty_petugas'              => $is_empty_petugas,
            'kegiatan_updated_at'           => $kegiatan_updated_at,
            'petugas_kegiatan_updated_at'   => $petugas_kegiatan_updated_at,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        // mengembalikan value honor yang sudah diformat dengan menghilangkan . sebagai pemisah ribuan, juta, dst.
        $request->merge([
            'honor_nias' => parseNominal($request->honor_nias),
        ]);

        $validated_data = $request->validate([
            'nama_kegiatan'     => 'required|string|max:255|not_regex:/[\/\\\]/',
            'is_ob'             => 'nullable|boolean',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
                function ($attribute, $value, $fail) use ($request) {
                    $tanggal_mulai      = Carbon::parse($request->tanggal_mulai);
                    $tanggal_selesai    = Carbon::parse($value);

                    if ($tanggal_mulai->month !== $tanggal_selesai->month || $tanggal_mulai->year !== $tanggal_selesai->year) {
                        $fail('Tanggal mulai dan tanggal selesai harus dalam bulan yang sama.');
                    }
                },
            ],
            'beban_anggaran'    => 'required|string|max:255',
            'tim_kerja_id'      => 'required|exists:tim_kerjas,id',
            'honor_nias'        => 'nullable|integer',
        ], [
            'nama_kegiatan.not_regex' => 'Nama kegiatan tidak boleh mengandung karakter garis miring!',
        ]);

        // user non-admin tidak boleh memindahkan kegiatan ke tim kerja lain
        if (! Auth::user()->isAdmin() && ! in_array((int) $validated_data['tim_kerja_id'], Auth::user()->timKerjaIds())) {
            abort(403, 'Anda tidak berhak memindahkan kegiatan ke tim kerja ini.');
        }

        // update nomor kontrak apabila terjadi perubahan bulan kegiatan
        $old_tanggal_mulai = $kegiatan->tanggal_mulai;
        $nomor_disesuaikan = false;

        DB::beginTransaction();
        try {
            $kegiatan->update($validated_data);

            // periksa apakah bulan di tanggal mulai berubah dan nomor kontrak sudah pernah digenerate
            if (Carbon::parse($old_tanggal_mulai)->format('Ym') !== Carbon::parse($validated_data['tanggal_mulai'])->format('Ym') && $kegiatan->is_generated) {

                NomorKontrak::where('kegiatan_id', $kegiatan->id)->delete();

                $kegiatan->is_generated = false;
                $kegiatan->save();

                // generate ulang nomor kontrak (best-effort: pemanggilan diabaikan bila belum ada petugas)
                $this->nomorKontrak->generate($kegiatan);
                $nomor_disesuaikan = true;
            }

            // Hitung ulang honor untuk petugas Wilayah Nias (1201). Nias Barat (1225) dinonaktifkan.
            $nias_id = WilayahTugas::where('kode_wilayah', WilayahTugas::KODE_NIAS)->value('id');

            if ($nias_id) {
                $petugas_nias = PetugasKegiatan::withTrashed()
                    ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
                    ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
                    ->where('mitras.wilayah_id', $nias_id)
                    ->select('petugas_kegiatans.id', 'petugas_kegiatans.beban_kerja')
                    ->get();

                foreach ($petugas_nias as $petugas) {
                    DB::table('petugas_kegiatans')
                        ->where('id', $petugas->id)
                        ->update([
                            'honor' => $this->honor->calculate($kegiatan, (int) $petugas->beban_kerja, WilayahTugas::KODE_NIAS),
                        ]);
                }
            }

            DB::commit();

            if ($nomor_disesuaikan) {
                return to_route('kegiatan.edit', $kegiatan->slug)
                    ->with('success', 'Kegiatan berhasil diedit! Nomor kontrak dan BAST juga telah disesuaikan.');
            }

            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('success', 'Kegiatan berhasil diedit!');
        } catch (\Throwable $th) {
            DB::rollBack();
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Terjadi kesalahan saat memperbarui kegiatan: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize('delete', $kegiatan);

        $kegiatan->delete();

        return to_route('kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus!');
    }
}
