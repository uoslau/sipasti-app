@php
    $delta = $stats['month_delta'];

    if ($stats['month_prev'] === 0 && $stats['month'] > 0) {
        $deltaIcon = 'bx-up-arrow-alt';
        $deltaClass = 'text-info';
        $deltaText = 'Baru bulan ini';
    } elseif ($delta > 0) {
        $deltaIcon = 'bx-up-arrow-alt';
        $deltaClass = 'text-success';
        $deltaText = '+' . $delta . ' dari bulan lalu';
    } elseif ($delta < 0) {
        $deltaIcon = 'bx-down-arrow-alt';
        $deltaClass = 'text-danger';
        $deltaText = $delta . ' dari bulan lalu';
    } else {
        $deltaIcon = 'bx-minus';
        $deltaClass = 'text-muted';
        $deltaText = 'Sama dengan bulan lalu';
    }

    $statusTotal = $stats['generated_year'] + $stats['pending_year'];
    $generatedPct = $statusTotal > 0 ? (int) round($stats['generated_year'] / $statusTotal * 100) : 0;

    $teamLabel = function ($k) {
        $alias = trim((string) optional($k->timKerja)->alias_tim_kerja);
        if ($alias !== '' && $alias !== 'null') {
            return $alias;
        }
        $fungsi = trim((string) optional($k->fungsi)->fungsi);
        return $fungsi !== '' && $fungsi !== 'null' ? $fungsi : 'Tanpa tim kerja';
    };
@endphp
<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y dash-page" data-dashboard-charts
        data-endpoint="{{ route('dashboard.data') }}">

        {{-- Judul & konteks --}}
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $greeting }}, {{ auth()->user()->name }}</h4>
                <div class="dash-context">
                    <span><i class="bx bx-calendar-event" aria-hidden="true"></i>{{ $todayLabel }}</span>
                    <span class="dash-context-sep" aria-hidden="true"></span>
                    <span><i class="bx bx-sitemap" aria-hidden="true"></i>{{ $scopeLabel }}</span>
                </div>
            </div>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-primary">
                <i class="bx bx-task me-1" aria-hidden="true"></i>Lihat kegiatan
            </a>
        </div>

        @if ($stats['total'] === 0)
            <div class="card">
                <div class="dash-empty">
                    <i class="bx bx-bar-chart-alt-2" aria-hidden="true"></i>
                    <h6 class="dash-empty-title">Belum ada data untuk dirangkum</h6>
                    <p class="dash-empty-text">
                        @if ($isAdmin)
                            Belum ada kegiatan yang dibuat. Angka dan grafik akan terisi setelah kegiatan pertama ditambahkan.
                        @else
                            Anda belum terdaftar di tim kerja mana pun, jadi belum ada kegiatan yang bisa dirangkum.
                        @endif
                    </p>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-primary">Lihat kegiatan</a>
                </div>
            </div>
        @else
            {{-- Ringkasan --}}
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card dash-stat h-100">
                        <div class="card-body">
                            <div class="dash-stat-head">
                                <span class="dash-stat-icon bg-label-primary" aria-hidden="true"><i
                                        class="bx bx-calendar-check"></i></span>
                                <span class="dash-stat-label">Kegiatan {{ $stats['reference_month'] }}</span>
                            </div>
                            <div class="dash-stat-value">{{ $stats['month'] }}</div>
                            <div class="dash-stat-meta {{ $deltaClass }}">
                                <i class="bx {{ $deltaIcon }}" aria-hidden="true"></i>{{ $deltaText }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card dash-stat h-100">
                        <div class="card-body">
                            <div class="dash-stat-head">
                                <span class="dash-stat-icon bg-label-success" aria-hidden="true"><i
                                        class="bx bx-wallet"></i></span>
                                <span class="dash-stat-label">Honor {{ $stats['reference_month'] }}</span>
                            </div>
                            <div class="dash-stat-value">{{ formatNominal($stats['honor']) }}</div>
                            <div class="dash-stat-meta text-muted">
                                <i class="bx bx-user" aria-hidden="true"></i>{{ $stats['mitra'] }} mitra ditugaskan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card dash-stat dash-stat-attention h-100">
                        <div class="card-body">
                            <div class="dash-stat-head">
                                <span class="dash-stat-icon bg-label-warning" aria-hidden="true"><i
                                        class="bx bx-error-circle"></i></span>
                                <span class="dash-stat-label">Belum digenerate</span>
                            </div>
                            <div class="dash-stat-value">{{ $stats['pending_generate'] }}</div>
                            <div class="dash-stat-meta text-muted">
                                <i class="bx bx-file" aria-hidden="true"></i>SPK &amp; BAST belum dibuat
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card dash-stat h-100">
                        <div class="card-body">
                            <div class="dash-stat-head">
                                <span class="dash-stat-icon bg-label-info" aria-hidden="true"><i
                                        class="bx bx-id-card"></i></span>
                                <span class="dash-stat-label">Mitra {{ $stats['reference_month'] }}</span>
                            </div>
                            <div class="dash-stat-value">{{ $stats['mitra'] }}</div>
                            <div class="dash-stat-meta text-muted">
                                <i class="bx bx-layer" aria-hidden="true"></i>{{ $stats['total'] }} kegiatan tercatat
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @unless ($stats['is_reference_current'])
                <p class="dash-note">
                    <i class="bx bx-info-circle" aria-hidden="true"></i>
                    Belum ada kegiatan pada bulan berjalan, jadi angka bulanan memakai bulan terakhir yang memiliki data.
                </p>
            @endunless

            {{-- Grafik --}}
            <noscript>
                <div class="alert alert-info" role="status">
                    Grafik memerlukan JavaScript. Angka ringkasan dan daftar di halaman ini tetap dapat dibaca.
                </div>
            </noscript>

            <div class="alert alert-warning d-none" id="dash-chart-error" role="status">
                Grafik tidak dapat dimuat. Angka ringkasan dan daftar di halaman ini tetap akurat.
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Kegiatan per bulan</h5>
                            <small class="text-muted">Jumlah kegiatan mulai, {{ $prevYear }} dan {{ $year }}</small>
                        </div>
                        <div class="card-body">
                            <div class="dash-chart-wrap">
                                <div class="dash-chart-skeleton" aria-hidden="true"></div>
                                <div id="dash-chart-monthly" class="dash-chart" role="img"
                                    aria-label="Grafik jumlah kegiatan per bulan untuk {{ $prevYear }} dan {{ $year }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Status SPK &amp; BAST</h5>
                            <small class="text-muted">Tahun {{ $year }}, {{ $statusTotal }} kegiatan</small>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="dash-chart-wrap dash-chart-wrap-donut">
                                    <div class="dash-chart-skeleton dash-chart-skeleton-round" aria-hidden="true"></div>
                                    <div id="dash-chart-status" class="dash-chart" role="img"
                                        aria-label="Komposisi kegiatan yang sudah dan belum digenerate pada {{ $year }}"></div>
                                </div>
                                <div>
                                    <div class="dash-donut-value">{{ $generatedPct }}%</div>
                                    <div class="dash-donut-label">sudah digenerate</div>
                                </div>
                            </div>
                            <ul class="dash-legend">
                                <li>
                                    <span class="dash-legend-dot dash-legend-dot-success" aria-hidden="true"></span>
                                    <span class="dash-legend-text">Sudah generate</span>
                                    <span class="dash-legend-value">{{ $stats['generated_year'] }}</span>
                                </li>
                                <li>
                                    <span class="dash-legend-dot dash-legend-dot-warning" aria-hidden="true"></span>
                                    <span class="dash-legend-text">Belum generate</span>
                                    <span class="dash-legend-value">{{ $stats['pending_year'] }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Petugas per wilayah</h5>
                            <small class="text-muted">{{ $stats['reference_month'] }}, {{ $wilayahTotal }} mitra</small>
                        </div>
                        <div class="card-body">
                            @if ($wilayahSplit->isEmpty())
                                <p class="text-muted mb-0">Belum ada mitra yang ditugaskan bulan ini.</p>
                            @else
                                <ul class="dash-regions">
                                    @foreach ($wilayahSplit as $wilayah)
                                        <li class="dash-region">
                                            <div class="dash-region-head">
                                                <span class="dash-legend-text">{{ $wilayah->label }}</span>
                                                <strong class="dash-region-value">{{ $wilayah->total }}</strong>
                                            </div>
                                            <div class="dash-bar" aria-hidden="true">
                                                <span style="width: {{ $wilayahTotal > 0 ? round($wilayah->total / $wilayahTotal * 100) : 0 }}%"></span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tindak lanjut & sebaran tim kerja --}}
            <div class="row g-4 mb-4">
                <div class="col-xl-7">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="mb-0">Perlu ditindaklanjuti</h5>
                                <small class="text-muted">Kegiatan yang belum digenerate SPK/BAST</small>
                            </div>
                            <span class="badge bg-label-warning">{{ $stats['pending_generate'] }}</span>
                        </div>
                        <div class="card-body">
                            @if ($pendingKegiatan->isEmpty())
                                <div class="dash-inline-empty">
                                    <i class="bx bx-check-circle" aria-hidden="true"></i>
                                    <span>Semua kegiatan sudah digenerate.</span>
                                </div>
                            @else
                                <ul class="dash-list">
                                    @foreach ($pendingKegiatan as $kegiatan)
                                        <li>
                                            <div class="dash-list-main">
                                                <a href="{{ route('kegiatan.edit', $kegiatan->slug) }}"
                                                    class="dash-list-title">{{ $kegiatan->nama_kegiatan }}</a>
                                                <span class="dash-list-meta">
                                                    {{ $teamLabel($kegiatan) }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->translatedFormat('d M Y') }}
                                                </span>
                                            </div>
                                            <i class="bx bx-chevron-right dash-list-arrow" aria-hidden="true"></i>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Sebaran kegiatan per tim kerja</h5>
                            <small class="text-muted">Tahun {{ $year }}</small>
                        </div>
                        <div class="card-body">
                            <div class="dash-chart-wrap">
                                <div class="dash-chart-skeleton" aria-hidden="true"></div>
                                <div id="dash-chart-teams" class="dash-chart" role="img"
                                    aria-label="Grafik jumlah kegiatan per tim kerja pada {{ $year }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kegiatan terbaru --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kegiatan terbaru</h5>
                    <small class="text-muted">Enam kegiatan dengan tanggal mulai terakhir</small>
                </div>
                <div class="table-responsive">
                    <table class="table dash-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Kegiatan</th>
                                <th scope="col">Tim kerja</th>
                                <th scope="col" class="text-center">Petugas</th>
                                <th scope="col" class="text-center">SPK &amp; BAST</th>
                                <th scope="col" class="text-end">Tanggal mulai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentKegiatan as $kegiatan)
                                <tr>
                                    <td>
                                        <a href="{{ route('kegiatan.edit', $kegiatan->slug) }}"
                                            class="dash-list-title">{{ $kegiatan->nama_kegiatan }}</a>
                                        @if ($kegiatan->is_ob)
                                            <span class="badge bg-label-warning ms-1">O-B</span>
                                        @endif
                                    </td>
                                    <td><span class="dash-chip">{{ $teamLabel($kegiatan) }}</span></td>
                                    <td class="text-center">{{ $kegiatan->petugas_kegiatan_count }}</td>
                                    <td class="text-center">
                                        @if ($kegiatan->is_generated)
                                            <span class="badge bg-label-success"><i
                                                    class="bx bx-check-circle me-1"></i>Sudah</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>Belum</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->translatedFormat('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layout>

<script src="{{ asset('js/dashboard.js') }}"></script>
