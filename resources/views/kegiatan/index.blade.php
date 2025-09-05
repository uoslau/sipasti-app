<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">Daftar Kegiatan</h5>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <form action="{{ route('kegiatan.index') }}" method="GET" class="mb-0">
                        <div class="input-group">
                            <span class="input-group-text bg-label-primary"><i class='bx bx-search'></i></span>
                            <input type="search" name="search" class="form-control" placeholder="Cari kegiatan..."
                                value="{{ $search ?? '' }}" />
                        </div>
                    </form>

                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKegiatanModal">
                        Tambah
                    </a>
                </div>
            </div>
            <div class="modal fade" id="addKegiatanModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addKegiatanModalLabel">
                                Tambah Kegiatan Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('kegiatan.create')
                        </div>
                    </div>
                </div>
            </div>
            @if ($kegiatan->isEmpty())
                <h5 class="card-header border-top text-center">Belum ada data.</h5>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%;"></th>
                                <th class="text-start" style="width: 45%;">Nama Kegiatan</th>
                                <th class="text-center" style="width: 15%;">Budget</th>
                                <th class="text-center" style="width: 10%;">Tim Kerja</th>
                                <th class="text-center" style="width: 20%;">Tanggal Pelaksanaan</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom">
                            @foreach ($kegiatan as $k)
                                <tr>
                                    @php
                                        $isGenerated = $k->is_generated;
                                        $badgeClass = $isGenerated ? 'success' : 'danger';
                                        $iconClass = $isGenerated ? 'bx bx-check-circle' : 'bx bx-x-circle';
                                        $titleText = $isGenerated ? 'sudah generate' : 'belum generate';
                                    @endphp
                                    <td>
                                        <span class="badge rounded-pill bg-label-{{ $badgeClass }}">
                                            <i class="{{ $iconClass }}" data-bs-toggle="tooltip"
                                                title="{{ $titleText }}"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <a href="{{ route('kegiatan.edit', $k->slug) }}"
                                                title="{{ $k->nama_kegiatan }}">
                                                {{ $k->nama_kegiatan }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-success">
                                            {{ formatNominal($k->petugas_kegiatan_sum_honor) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary">
                                            {{ $k->timkerja->alias_tim_kerja }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-warning">
                                            {{ $k->tanggal_mulai }}
                                        </span>
                                        /
                                        <span class="badge bg-label-warning">
                                            {{ $k->tanggal_selesai }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('kegiatan.edit', $k->slug) }}">Edit</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider" />
                                                </li>
                                                <li>
                                                    <form action="{{ route('kegiatan.destroy', $k->slug) }}"
                                                        method="POST" class="delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="dropdown-item btn-delete-kegiatan">Hapus
                                                        </button>
                                                    </form>
                                                </li>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="pagination pagination-sm justify-content-end pt-3">
                        {{ $kegiatan->onEachSide(0)->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>

<script src="{{ asset('js/confirm-delete.js') }}"></script>
