<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">List Kegiatan</h5>
                <a href="#" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#addKegiatanModal">
                    Tambah Kegiatan
                </a>
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
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Kegiatan</th>
                            <th class="text-center">Budget</th>
                            <th class="text-center">Tim Kerja</th>
                            <th class="text-center">Mulai / Selesai</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom">
                        @foreach ($kegiatan as $k)
                            <tr>
                                <td
                                    style="max-width: 60ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <a href="{{ route('kegiatan.edit', $k->slug) }}">{{ $k->nama_kegiatan }}</a>
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
                                                <a class="dropdown-item {{ $k->is_generated ? '' : 'disabled' }}"
                                                    href="javascript:void(0);">Unduh</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('kegiatan.destroy', $k->slug) }}" method="POST"
                                                    class="delete-form">
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
        </div>
    </div>
</x-layout>

<script src="{{ asset('js/confirm-delete.js') }}"></script>
