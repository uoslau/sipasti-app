<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PetugasKegiatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** @var array<int, int>|null */
    private ?array $teamIds = null;

    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        $now = Carbon::now();
        $year = (int) $now->year;
        $prevYear = $year - 1;

        // Bulan acuan: bulan berjalan bila ada datanya, jika tidak maka bulan terakhir yang memiliki data.
        $referenceMonth = $this->referenceMonth($user);
        $referenceStart = $referenceMonth->copy()->startOfMonth();
        $referenceEnd = $referenceMonth->copy()->endOfMonth();
        $prevReferenceStart = $referenceMonth->copy()->subMonthNoOverflow()->startOfMonth();
        $prevReferenceEnd = $referenceMonth->copy()->subMonthNoOverflow()->endOfMonth();

        $monthCount = $this->kegiatanQuery($user)
            ->whereBetween('tanggal_mulai', [$referenceStart, $referenceEnd])
            ->count();

        $prevMonthCount = $this->kegiatanQuery($user)
            ->whereBetween('tanggal_mulai', [$prevReferenceStart, $prevReferenceEnd])
            ->count();

        $petugasReferenceMonth = $this->petugasQuery($user)
            ->whereBetween('kegiatans.tanggal_mulai', [$referenceStart, $referenceEnd]);

        $honorReference = (int) (clone $petugasReferenceMonth)->sum('petugas_kegiatans.honor');
        $mitraReference = (clone $petugasReferenceMonth)->distinct()->count('petugas_kegiatans.nik');

        $wilayahSplit = (clone $petugasReferenceMonth)
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->leftJoin('wilayah_tugas', 'mitras.wilayah_id', '=', 'wilayah_tugas.id')
            ->groupBy('wilayah_tugas.nama_wilayah')
            ->orderByDesc('total')
            ->selectRaw("COALESCE(wilayah_tugas.nama_wilayah, 'Belum ada wilayah') as label, COUNT(DISTINCT petugas_kegiatans.nik) as total")
            ->get();

        $generatedThisYear = $this->kegiatanQuery($user)
            ->whereYear('tanggal_mulai', $year)
            ->where('is_generated', true)
            ->count();

        $pendingThisYear = $this->kegiatanQuery($user)
            ->whereYear('tanggal_mulai', $year)
            ->where('is_generated', false)
            ->count();

        return view('dashboard.index', [
            'isAdmin'       => $isAdmin,
            'greeting'      => $this->greeting(),
            'scopeLabel'    => $this->scopeLabel($user),
            'todayLabel'    => $now->translatedFormat('l, d F Y'),
            'year'          => $year,
            'prevYear'      => $prevYear,
            'stats'         => [
                'month'            => $monthCount,
                'month_delta'      => $monthCount - $prevMonthCount,
                'month_prev'       => $prevMonthCount,
                'reference_month'  => $referenceMonth->translatedFormat('F Y'),
                'is_reference_current' => $referenceMonth->isSameMonth($now),
                'honor'            => $honorReference,
                'mitra'            => $mitraReference,
                'pending_generate' => $this->kegiatanQuery($user)->where('is_generated', false)->count(),
                'generated_year'   => $generatedThisYear,
                'pending_year'     => $pendingThisYear,
                'total'            => $this->kegiatanQuery($user)->count(),
            ],
            'wilayahSplit'   => $wilayahSplit,
            'wilayahTotal'   => (int) $wilayahSplit->sum('total'),
            'pendingKegiatan' => $this->kegiatanQuery($user)
                ->with(['timKerja', 'fungsi'])
                ->where('is_generated', false)
                ->orderByDesc('tanggal_mulai')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'recentKegiatan' => $this->kegiatanQuery($user)
                ->with(['timKerja', 'fungsi'])
                ->withCount('petugasKegiatan')
                ->orderByDesc('tanggal_mulai')
                ->orderByDesc('id')
                ->limit(6)
                ->get(),
        ]);
    }

    /**
     * Data grafik dashboard (di-scope sesuai tim kerja user).
     */
    public function getChartData()
    {
        $user = Auth::user();
        $year = (int) Carbon::now()->year;
        $prevYear = $year - 1;

        return response()->json([
            'monthly' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'series'     => [
                    ['name' => (string) $year, 'data' => $this->monthlyCounts($user, $year)],
                    ['name' => (string) $prevYear, 'data' => $this->monthlyCounts($user, $prevYear)],
                ],
            ],
            'status'  => [
                'generated' => $this->kegiatanQuery($user)->whereYear('tanggal_mulai', $year)->where('is_generated', true)->count(),
                'pending'   => $this->kegiatanQuery($user)->whereYear('tanggal_mulai', $year)->where('is_generated', false)->count(),
            ],
            'teams'   => $this->teamBreakdown($user, $year),
        ]);
    }

    /**
     * Jumlah kegiatan per bulan pada satu tahun.
     *
     * @return array<int, int>
     */
    private function monthlyCounts(User $user, int $year): array
    {
        $counts = $this->kegiatanQuery($user)
            ->whereYear('tanggal_mulai', $year)
            ->selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $data = array_fill(0, 12, 0);

        foreach ($counts as $bulan => $total) {
            $data[(int) $bulan - 1] = (int) $total;
        }

        return $data;
    }

    /**
     * Sebaran jumlah kegiatan per tim kerja pada satu tahun.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function teamBreakdown(User $user, int $year): array
    {
        $rows = $this->kegiatanQuery($user)
            ->join('tim_kerjas', 'kegiatans.tim_kerja_id', '=', 'tim_kerjas.id')
            ->whereYear('kegiatans.tanggal_mulai', $year)
            ->groupBy('tim_kerjas.id', 'tim_kerjas.nama_tim_kerja', 'tim_kerjas.alias_tim_kerja')
            ->orderByDesc('total')
            ->selectRaw('tim_kerjas.nama_tim_kerja, tim_kerjas.alias_tim_kerja, COUNT(*) as total')
            ->limit(8)
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $alias = trim((string) $row->alias_tim_kerja);
            $nama = trim((string) $row->nama_tim_kerja);
            $isPlaceholder = fn (?string $v) => $v === null || $v === '' || $v === 'null';

            $labels[] = ! $isPlaceholder($alias) ? $alias : (! $isPlaceholder($nama) ? $nama : 'Lainnya');
            $data[] = (int) $row->total;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Query kegiatan yang sudah dibatasi ke tim kerja user (admin melihat semua).
     */
    private function kegiatanQuery(User $user): Builder
    {
        return Kegiatan::query()
            ->when(! $user->isAdmin(), fn (Builder $q) => $q->whereIn('tim_kerja_id', $this->teamIds($user)));
    }

    /**
     * Query petugas kegiatan (ter-join ke kegiatan) yang sudah dibatasi ke tim kerja user.
     */
    private function petugasQuery(User $user): Builder
    {
        return PetugasKegiatan::query()
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->whereNull('kegiatans.deleted_at')
            ->when(! $user->isAdmin(), fn (Builder $q) => $q->whereIn('kegiatans.tim_kerja_id', $this->teamIds($user)));
    }

    /**
     * @return array<int, int>
     */
    private function teamIds(User $user): array
    {
        return $this->teamIds ??= $user->timKerjaIds();
    }

    /**
     * Bulan acuan untuk angka bulanan: bulan berjalan bila ada datanya,
     * jika tidak maka bulan terakhir yang memiliki data.
     */
    private function referenceMonth(User $user): Carbon
    {
        $current = Carbon::now()->startOfMonth();

        $hasCurrent = $this->kegiatanQuery($user)
            ->whereBetween('tanggal_mulai', [$current->copy()->startOfMonth(), $current->copy()->endOfMonth()])
            ->exists();

        if ($hasCurrent) {
            return $current;
        }

        $latest = $this->kegiatanQuery($user)->max('tanggal_mulai');

        return $latest ? Carbon::parse($latest)->startOfMonth() : $current;
    }

    private function scopeLabel(User $user): string
    {
        if ($user->isAdmin()) {
            return 'Semua tim kerja';
        }

        $aliases = $user->timKerja()
            ->pluck('alias_tim_kerja')
            ->map(fn ($alias) => trim((string) $alias))
            ->reject(fn ($alias) => $alias === '' || $alias === 'null')
            ->values();

        if ($aliases->isEmpty()) {
            return 'Belum terdaftar di tim kerja';
        }

        return 'Tim kerja: ' . $aliases->implode(', ');
    }

    private function greeting(): string
    {
        $hour = (int) Carbon::now()->format('H');

        if ($hour < 11) {
            return 'Selamat pagi';
        }

        if ($hour < 15) {
            return 'Selamat siang';
        }

        if ($hour < 19) {
            return 'Selamat sore';
        }

        return 'Selamat malam';
    }
}
