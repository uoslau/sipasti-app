{{-- SEMENTARA — fitur Sensus Ekonomi. --}}
{{-- Daftar pilihan filter yang sudah disesuaikan dengan pilihan di atasnya; dibaca ulang oleh JS
     setiap kali potongan ini dimuat, sehingga dropdown menyempit tanpa request tambahan. --}}
<script type="application/json" data-sensus-options>@json(['options' => $filterOptions ?? [], 'selected' => $filters ?? []])</script>
@php
    $isSearching = $term !== null && trim($term) !== '';
    $activeFilters = collect($filters ?? [])->filter(fn ($value) => trim((string) $value) !== '');

    // Nilai dan kata kunci sama-sama di-escape sebelum disorot, jadi aman untuk output mentah.
    $mark = function ($value) use ($term) {
        if ($value === null || trim((string) $value) === '') {
            return '<span class="sensus-muted">&mdash;</span>';
        }

        $escaped = e($value);

        if ($term === null || trim($term) === '') {
            return $escaped;
        }

        return preg_replace('/' . preg_quote(e($term), '/') . '/iu', '<mark>$0</mark>', $escaped) ?? $escaped;
    };

    $palette = ['primary', 'info', 'success', 'warning', 'danger', 'dark'];

    $initials = function ($name) {
        $words = collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2);

        return $words->isEmpty()
            ? '?'
            : $words->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
    };

    $breadcrumb = fn (...$parts) => collect($parts)
        ->map(fn ($part) => trim((string) $part))
        ->reject(fn ($part) => $part === '' || $part === 'null')
        ->values();
@endphp

<div class="sensus-resultbar" aria-live="polite">
    <span class="sensus-resultbar-text">
        @if ($isSearching)
            <strong>{{ number_format($results->total(), 0, ',', '.') }}</strong> hasil untuk
            &ldquo;{{ $term }}&rdquo;
            @if ($activeFilters->isNotEmpty())
                &middot; {{ $activeFilters->count() }} filter aktif
            @endif
        @elseif ($activeFilters->isNotEmpty())
            <strong>{{ number_format($results->total(), 0, ',', '.') }}</strong> baris cocok filter
            &middot; {{ $activeFilters->count() }} filter aktif
            @if ($results->total() > 0)
                &middot; menampilkan {{ $results->firstItem() }}&ndash;{{ $results->lastItem() }}
            @endif
        @else
            <strong>{{ number_format($results->total(), 0, ',', '.') }}</strong> baris tersimpan
            @if ($results->total() > 0)
                &middot; menampilkan {{ $results->firstItem() }}&ndash;{{ $results->lastItem() }}
            @endif
        @endif
    </span>

    @if ($results->hasPages())
        <span class="sensus-resultbar-page">Halaman {{ $results->currentPage() }} /
            {{ $results->lastPage() }}</span>
    @endif
</div>

@if ($results->isEmpty())
    <div class="sensus-noresult">
        <span class="sensus-noresult-art" aria-hidden="true"><i
                class="bx {{ $isSearching ? 'bx-search-alt' : 'bx-inbox' }}"></i></span>
        @if ($isSearching)
            <h6>Tidak ketemu: &ldquo;{{ $term }}&rdquo;</h6>
            <p>Ejaan lain atau kata yang lebih pendek biasanya lebih berhasil. Kolom pencarian juga bisa diganti.</p>
        @elseif ($activeFilters->isNotEmpty())
            <h6>Tidak ada baris yang cocok dengan filter</h6>
            <p>Longgarkan atau bersihkan sebagian filter, lalu coba lagi.</p>
        @else
            <h6>Belum ada data</h6>
            <p>Import file Excel untuk mulai menampilkan data.</p>
        @endif
    </div>
@else
    <ul class="sensus-records">
        @foreach ($results as $index => $row)
            @php
                $namaKk = trim((string) $row->nama_kk);
                $namaDtsen = trim((string) $row->nama_dtsen);
                $judul = $namaKk !== '' ? $namaKk : ($namaDtsen !== '' ? $namaDtsen : '');
                $lokasi = $breadcrumb($row->nama_kecamatan, $row->level_4_name, $row->level_5_name);
                // Warna avatar dari nama KK supaya anggota rumah tangga yang sama tampil sewarna.
                $avatarColor = $palette[abs(crc32($judul !== '' ? $judul : (string) $row->nik_dtsen)) % count($palette)];
                $linkFasih = trim((string) $row->link_fasih);
                $linkValid = str_starts_with($linkFasih, 'http://') || str_starts_with($linkFasih, 'https://');
                $catatan = trim((string) $row->catatan);
            @endphp

            <li class="sensus-record" style="--i: {{ min($index, 10) }}">
                <span class="sensus-avatar bg-label-{{ $avatarColor }}" aria-hidden="true">{{ $initials($judul) }}</span>

                <div class="sensus-record-body">
                    <div class="sensus-record-top">
                        <span class="sensus-record-title">
                            @if ($namaKk !== '')
                                {!! $mark($namaKk) !!}
                            @else
                                <span class="sensus-muted">Tanpa nama KK</span>
                            @endif
                        </span>

                        @if (trim((string) $row->ada_keluarga_label) !== '')
                            <span class="sensus-chip" title="ada_keluarga_label">{{ $row->ada_keluarga_label }}</span>
                        @endif
                    </div>

                    <div class="sensus-record-kv">
                        <span class="sensus-kv">
                            <span class="sensus-kv-key">DTSEN</span>
                            <span class="sensus-kv-val">{!! $mark($namaDtsen) !!}</span>
                        </span>
                        <span class="sensus-kv">
                            <span class="sensus-kv-key">NIK</span>
                            <span class="sensus-kv-val sensus-nik">{!! $mark($row->nik_dtsen) !!}</span>
                        </span>
                    </div>

                    <div class="sensus-record-meta">
                        @if ($lokasi->isNotEmpty())
                            <span class="sensus-meta-item">
                                <i class="bx bx-map-pin" aria-hidden="true"></i>{{ $lokasi->implode(' › ') }}
                            </span>
                        @endif

                        @if (trim((string) $row->level_2_full_code) !== '')
                            <span class="sensus-code" title="level_2_full_code">{{ $row->level_2_full_code }}</span>
                        @endif

                        @if (trim((string) $row->email_pencacah) !== '')
                            <span class="sensus-meta-item">
                                <i class="bx bx-envelope" aria-hidden="true"></i>{{ $row->email_pencacah }}
                            </span>
                        @endif
                    </div>

                    @if ($catatan !== '')
                        <div class="sensus-record-note" title="{{ $catatan }}">
                            <i class="bx bx-note" aria-hidden="true"></i>
                            <span>{{ $catatan }}</span>
                        </div>
                    @endif
                </div>

                <div class="sensus-record-side">
                    @if ($linkValid)
                        <a href="{{ $linkFasih }}" target="_blank" rel="noopener noreferrer" class="sensus-fasih"
                            title="{{ $linkFasih }}">
                            <i class="bx bx-link-external" aria-hidden="true"></i>Fasih
                        </a>
                    @elseif ($linkFasih !== '')
                        <span class="sensus-fasih sensus-fasih-plain" title="{{ $linkFasih }}">{{ $linkFasih }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>

    @if ($results->hasPages())
        <div class="sensus-pager">
            <button type="button" class="sensus-page-btn" data-sensus-page="{{ $results->currentPage() - 1 }}"
                @disabled($results->onFirstPage())>
                <i class="bx bx-chevron-left" aria-hidden="true"></i>Sebelumnya
            </button>

            <span class="sensus-pager-info">{{ $results->currentPage() }} / {{ $results->lastPage() }}</span>

            <button type="button" class="sensus-page-btn" data-sensus-page="{{ $results->currentPage() + 1 }}"
                @disabled(! $results->hasMorePages())>
                Berikutnya<i class="bx bx-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    @endif
@endif
