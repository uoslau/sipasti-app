<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session()->has('success'))
            <script>
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#696cff',
                });
            </script>
        @endif
        @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menambahkan Kegiatan',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#696cff',
                });
            </script>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">List Kegiatan</h5>
                <a href="#" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#addKegiatanModal"
                    style="margin-right: 20px;">
                    + Kegiatan
                </a>
                <div class="modal fade" id="addKegiatanModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="kegiatanModalLabel">
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
                                    <a href="{{ route('kegiatan.show', $k->slug) }}">{{ $k->nama_kegiatan }}</a>
                                </td>
                                <td></td>
                                {{-- <td>{{ formatNominal($kegiatan->honor_nias) }}</td> --}}
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
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('kegiatan.edit', $k->slug) }}"><i
                                                        class='bx bxs-edit'></i>Edit</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);"><i
                                                        class='bx bxs-download'></i>BAST</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('kegiatan.destroy', $k->slug) }}" method="POST"
                                                    class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item btn-delete-kegiatan">
                                                        <i class='bx bxs-trash'></i> Hapus
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
            </div>
        </div>
    </div>
</x-layout>
