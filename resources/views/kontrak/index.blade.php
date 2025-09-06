<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <form method="GET" action="{{ route('kontrak.index') }}">
                @csrf
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0">Daftar Mitra</h5>
                        <select id="bulan" name="bulan" class="form-select w-auto" onchange="this.form.submit()">
                            @foreach ($nama_bulan as $key => $bulan)
                                <option value="{{ $key }}" {{ $key == $bulan_sekarang ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                        <select id="tahun" name="tahun" class="form-select w-auto" onchange="this.form.submit()">
                            @foreach ($tahun_range as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == $tahun_sekarang ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-label-primary"><i class='bx bx-search'></i></span>
                            <input class="form-control" type="search" placeholder="Cari mitra" id="filter-nama" />
                        </div>
                        <a href="#" id="downloadButton" class="btn btn-primary">Unduh</a>
                    </div>
                </div>
            </form>
            @if ($petugas_bulan->isEmpty())
                <h5 class="card-header border-top text-center">Belum ada data.</h5>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkboxDashboard" />
                                    </div>
                                </th>
                                <th class="text-start px-0" style="width: 45%;">Nama</th>
                                <th class="text-center" style="width: 15%;">Honor</th>
                                <th class="text-center" style="width: 15%;">Sisa Honor</th>
                                <th class="text-center" style="width: 15%;">Bisa Dibayarkan</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom">
                            @foreach ($petugas_bulan as $p)
                                <tr class="clickable-row" data-target="#details-{{ $loop->index }}"
                                    data-toggle="collapse">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkboxDashboard"
                                                data-id="{{ $p['nik'] }}" />
                                        </div>
                                    </td>
                                    <td class="px-0">
                                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ ucwords(strtolower($p['nama_mitra'])) }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary">
                                            {{ formatNominal($p['total_honor']) }}
                                        </span>
                                        @if ($p['melebihi'])
                                            <i class='bx bx-info-circle' data-bs-toggle="tooltip"
                                                title="Sudah melewati batas honor bulan ini"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-danger">
                                            {{ formatNominal($p['sisa_honor']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-success">
                                            @if ($p['melebihi'])
                                                {{ formatNominal($p['honor_max']) }}
                                            @else
                                                {{ formatNominal($p['total_honor']) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center p-0">
                                        <i class="menu-icon tf-icons bx bx-chevron-down toggle-arrow"></i>
                                    </td>
                                </tr>
                                @include('kontrak.partials.detail-kegiatan-petugas')
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.clickable-row');
        const checkboxes = document.querySelectorAll('.form-check-input');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const targetId = row.getAttribute('data-target');
                const targetRow = document.querySelector(targetId);
                const arrow = row.querySelector('.toggle-arrow');

                if (targetRow.classList.contains('show')) {
                    targetRow.classList.remove('show');
                    arrow.style.transform = 'rotate(0deg)';
                    row.classList.remove('active-row');
                } else {
                    targetRow.classList.add('show');
                    arrow.style.transform = 'rotate(180deg)';
                    row.classList.add('active-row');
                }
            });
        });
    });
</script>

<script src="{{ asset('js/filter-nama.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('checkboxDashboard');
        const checkboxes = document.querySelectorAll('tbody .form-check-input');

        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const downloadButton = document.getElementById("downloadButton");

        downloadButton.addEventListener("click", function(event) {
            event.preventDefault();

            const selectedCheckboxes = document.querySelectorAll(
                "tbody .form-check-input:checked"
            );

            let selectedIds = [];
            selectedCheckboxes.forEach((checkbox) => {
                selectedIds.push(checkbox.dataset.id);
            });

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak ada petugas yang dipilih untuk diunduh SPK-nya.",
                    confirmButtonColor: '#696cff',
                });
                return;
            }

            const url = `{{ route('kontrak.download', $slug) }}?ids=${selectedIds.join(",")}`;

            window.location.href = url;
        });
    });
</script>
