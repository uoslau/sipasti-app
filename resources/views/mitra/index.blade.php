<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <!-- Form Filter (Otomatis Submit saat Tahun diubah) -->
            <form method="GET" action="{{ route('mitra.index') }}">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0">Rekap Beban Kerja Mitra</h5>

                        <!-- Filter Tahun -->
                        <select id="tahun" name="tahun" class="form-select w-auto" onchange="this.form.submit()">
                            {{-- Looping tahun dari 3 tahun lalu hingga 2 tahun ke depan --}}
                            @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Ubah href="#" menjadi memanggil route -->
                        <a href="{{ route('mitra.export', ['tahun' => $tahun]) }}" id="downloadButton"
                            class="btn btn-primary">
                            <i class='bx bx-spreadsheet me-1'></i> Export Excel
                        </a>
                    </div>
                </div>
            </form>

            @if ($mitras->isEmpty())
                <h5 class="card-header border-top text-center">Belum ada data pada tahun {{ $tahun }}.</h5>
            @else
                <div class="table-responsive text-nowrap">
                    <!-- Tambahkan table-bordered jika ingin ada garis pembatas vertikal antar bulan -->
                    <table class="table table-hover table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-start">Nama Mitra</th>
                                @php
                                    $bulanLabels = [
                                        'Jan',
                                        'Feb',
                                        'Mar',
                                        'Apr',
                                        'Mei',
                                        'Jun',
                                        'Jul',
                                        'Agt',
                                        'Sep',
                                        'Okt',
                                        'Nov',
                                        'Des',
                                    ];
                                @endphp
                                @foreach ($bulanLabels as $bulan)
                                    <th class="text-center px-2" style="width: 5%;">{{ $bulan }}</th>
                                @endforeach
                                <th class="text-center font-bold" style="width: 5%;">Total</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($mitras as $mitra)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="px-3 fw-semibold">
                                        <div
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                            {{ ucwords(strtolower($mitra['nama'])) }}
                                        </div>
                                    </td>

                                    {{-- Looping data bulanan dari index 1 sampai 12 --}}
                                    @for ($i = 1; $i <= 12; $i++)
                                        @php
                                            $jumlah = $mitra['rekap_bulanan'][$i];
                                        @endphp
                                        <td class="text-center px-1">
                                            @if ($jumlah > 0)
                                                <!-- Menggunakan badge primary bawaan template untuk angka > 0 -->
                                                <span class="badge bg-label-primary">{{ $jumlah }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endfor

                                    <td class="text-center">
                                        <!-- Menggunakan badge success untuk total keseluruhan -->
                                        <span class="badge bg-label-success fw-bold">
                                            {{ $mitra['total_kegiatan'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="pagination pagination-sm justify-content-end px-5 py-3">
                        {{ $mitras->onEachSide(0)->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
