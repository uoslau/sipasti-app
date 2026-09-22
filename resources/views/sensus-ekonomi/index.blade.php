{{-- SEMENTARA — fitur Sensus Ekonomi. --}}
@php
    $filterFields = [
        'nama_kecamatan' => 'Kecamatan',
        'level_4_name' => 'Desa',
        'level_5_name' => 'SLS',
        'email_pencacah' => 'Pencacah',
    ];

    $activeFilters = collect($filters ?? [])->filter(fn ($value) => trim((string) $value) !== '');
    $activeFilterCount = $activeFilters->count();
@endphp
<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y sensus-page" data-sensus
        data-endpoint="{{ route('sensus-ekonomi.results') }}"
        data-page-url="{{ route('sensus-ekonomi.index') }}">

        {{-- Judul --}}
        <div class="sensus-head">
            <div>
                <div class="sensus-head-title">
                    <h4>Sensus Ekonomi</h4>
                    <span class="sensus-tag">sementara</span>
                </div>
                <p>Telusuri data KK, DTSEN, dan NIK hasil import file Excel.</p>
            </div>

            @if ($total > 0)
                <div class="sensus-head-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#sensusImportModal">
                        <i class="bx bx-upload me-1" aria-hidden="true"></i>Import ulang
                    </button>
                    <form action="{{ route('sensus-ekonomi.destroy') }}" method="POST" id="sensus-reset-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger" id="sensus-reset-btn">
                            <i class="bx bx-trash me-1" aria-hidden="true"></i>Kosongkan
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if ($total === 0)
            {{-- Belum ada data --}}
            <div class="sensus-blank-card">
                <span class="sensus-blank-art" aria-hidden="true"><i class="bx bx-spreadsheet"></i></span>
                <h5>Belum ada data untuk ditelusuri</h5>
                <p>Import file Excel sensus ekonomi. Setelah masuk, data bisa dicari langsung berdasarkan nama KK, nama
                    DTSEN, atau NIK.</p>

                <div class="sensus-import-box">
                    @include('sensus-ekonomi.partials.import-form')
                </div>
            </div>
        @else
            {{-- Pencarian + tombol filter --}}
            <section class="sensus-hero">
                <div class="sensus-hero-glow" aria-hidden="true"></div>

                <div class="sensus-hero-body">
                    <label class="visually-hidden" for="sensus-search">Cari data sensus ekonomi</label>
                    <div class="sensus-search">
                        <i class="bx bx-search" aria-hidden="true"></i>
                        <input type="search" id="sensus-search" data-sensus-search autocomplete="off"
                            placeholder="Ketik nama KK, nama DTSEN, atau NIK..." />
                        <button type="button" class="sensus-search-clear d-none" data-sensus-clear
                            aria-label="Bersihkan pencarian">
                            <i class="bx bx-x" aria-hidden="true"></i>
                        </button>
                        <span class="sensus-spinner d-none" data-sensus-spinner aria-hidden="true"></span>
                    </div>

                    <div class="sensus-hero-tools">
                        <div class="sensus-segments" role="group" aria-label="Batasi pencarian ke kolom">
                            <span class="sensus-segments-label">Cari di</span>
                            <button type="button" class="sensus-segment is-active" data-sensus-column=""
                                aria-pressed="true">Semua kolom</button>
                            <button type="button" class="sensus-segment" data-sensus-column="nama_kk"
                                aria-pressed="false">Nama KK</button>
                            <button type="button" class="sensus-segment" data-sensus-column="nama_dtsen"
                                aria-pressed="false">Nama DTSEN</button>
                            <button type="button" class="sensus-segment" data-sensus-column="nik_dtsen"
                                aria-pressed="false">NIK</button>
                        </div>

                        <button type="button"
                            class="sensus-filter-btn {{ $activeFilterCount > 0 ? 'is-active' : '' }}"
                            data-bs-toggle="offcanvas" data-bs-target="#sensusFilterDrawer"
                            aria-controls="sensusFilterDrawer">
                            <i class="bx bx-filter-alt" aria-hidden="true"></i>Filter
                            <span
                                class="sensus-filter-btn-count {{ $activeFilterCount > 0 ? '' : 'd-none' }}"
                                data-sensus-filter-btn-count>{{ $activeFilterCount }}</span>
                        </button>
                    </div>

                    {{-- Rincian filter yang sedang aktif, supaya tetap terlihat walau panelnya tertutup --}}
                    <div class="sensus-active-filters {{ $activeFilterCount > 0 ? '' : 'd-none' }}"
                        data-sensus-active-filters data-sensus-filter-labels='@json($filterFields)'>
                        @foreach ($activeFilters as $field => $value)
                            <span class="sensus-active-chip">
                                <span class="sensus-active-chip-key">{{ $filterFields[$field] ?? $field }}</span>
                                <span class="sensus-active-chip-val">{{ $value }}</span>
                                <button type="button" class="sensus-active-chip-x" data-sensus-remove="{{ $field }}"
                                    aria-label="Hapus filter {{ $filterFields[$field] ?? $field }}">&times;</button>
                            </span>
                        @endforeach
                    </div>
                </div>

                <dl class="sensus-metrics">
                    <div class="sensus-metric">
                        <dt>Baris</dt>
                        <dd>{{ number_format($total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="sensus-metric">
                        <dt>Kecamatan</dt>
                        <dd>{{ number_format($kecamatanCount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="sensus-metric">
                        <dt>Pencacah</dt>
                        <dd>{{ number_format($pencacahCount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="sensus-metric">
                        <dt>Import terakhir</dt>
                        @if ($lastImported)
                            @php $importedAt = \Carbon\Carbon::parse($lastImported); @endphp
                            <dd class="sensus-metric-datetime"
                                title="{{ $importedAt->translatedFormat('l, d F Y, H:i:s') }}">
                                {{ $importedAt->translatedFormat('d M Y') }}<span
                                    class="sensus-metric-clock">{{ $importedAt->format('H:i:s') }}</span>
                            </dd>
                        @else
                            <dd>&mdash;</dd>
                        @endif
                    </div>
                </dl>
            </section>

            {{-- Hasil --}}
            <section class="sensus-results">
                <div data-sensus-results>
                    @include('sensus-ekonomi.partials.results', [
                        'results' => $results,
                        'term' => $term,
                        'column' => $column,
                        'filters' => $filters,
                        'filterOptions' => $filterOptions,
                    ])
                </div>
            </section>

            {{-- Panel filter, muncul dari kanan --}}
            <div class="offcanvas offcanvas-end sensus-drawer" tabindex="-1" id="sensusFilterDrawer"
                aria-labelledby="sensusFilterDrawerTitle">
                <div class="offcanvas-header sensus-drawer-head">
                    <div>
                        <h5 class="offcanvas-title mb-0" id="sensusFilterDrawerTitle">Filter</h5>
                        <small>Pilih berurutan dari atas ke bawah, pilihan menyesuaikan</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                        aria-label="Tutup panel filter"></button>
                </div>

                <div class="offcanvas-body sensus-drawer-body">
                    @foreach ($filterFields as $field => $label)
                        @php $options = $filterOptions[$field] ?? []; @endphp
                        <div class="sensus-filter">
                            <label class="sensus-filter-label" for="sensus-filter-{{ $field }}">
                                <span class="sensus-filter-step">{{ $loop->iteration }}</span>
                                {{ $label }}
                                <span class="sensus-filter-count"
                                    data-sensus-filter-count="{{ $field }}">{{ count($options) }}</span>
                            </label>
                            <select id="sensus-filter-{{ $field }}" class="form-select sensus-filter-select"
                                data-sensus-filter="{{ $field }}"
                                data-sensus-placeholder="Semua {{ strtolower($label) }}">
                                <option value="">Semua {{ strtolower($label) }}</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}"
                                        @selected(($filters[$field] ?? '') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="sensus-drawer-foot">
                    <button type="button"
                        class="sensus-filters-clear {{ $activeFilterCount > 0 ? '' : 'd-none' }}"
                        data-sensus-filters-clear>
                        <i class="bx bx-x" aria-hidden="true"></i>Bersihkan filter
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas">Selesai</button>
                </div>
            </div>

            {{-- Modal import ulang --}}
            <div class="modal fade" id="sensusImportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Import ulang data sensus ekonomi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            @include('sensus-ekonomi.partials.import-form')
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layout>

<script src="{{ asset('js/sensus-ekonomi.js') }}"></script>
