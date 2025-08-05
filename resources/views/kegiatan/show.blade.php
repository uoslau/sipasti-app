{{-- @dd($petugas_kegiatan) --}}
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
                <h5 class="mb-0 me-2">{{ $nama_kegiatan }}</h5>
                <a href="#" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#addPetugasModal"
                    style="margin-right: 20px;">
                    + Petugas
                </a>
                <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel3">Tambah
                                    Petugas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{-- @include('petugas.create', ['slug' => $slug]) --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($petugas_kegiatan->isEmpty())
                <h5 class="card-header border-top text-center">Belum ada petugas untuk kegiatan ini.</h5>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Mitra</th>
                                <th class="text-center">Tugas / Beban</th>
                                <th class="text-center">Wilayah Tugas</th>
                                <th class="text-center">Honor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom">
                            @foreach ($petugas_kegiatan as $p)
                                <tr>
                                    <td
                                        style="max-width: 500px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ ucwords(strtolower($p->nama_mitra)) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-label-danger">
                                            {{ $p->bertugas_sebagai }} / {{ $p->beban_kerja }}
                                            {{ $p->satuan }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-label-primary">
                                            {{ $p->wilayah_tugas == '1201' ? 'Nias' : 'Nias Barat' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-label-success">
                                            Rp. {{ number_format($p->honor, 0, '.', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                {{-- <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('petugas.edit', [$slug, $p->id]) }}"><i
                                                            class='bx bxs-edit'></i>Edit</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider" />
                                                </li>
                                                <li>
                                                    <form action="{{ route('petugas.destroy', [$slug, $p->sktnp]) }}"
                                                        method="POST">
                                                        @method('delete')
                                                        @csrf
                                                        <button class="dropdown-item"
                                                            onclick="return confirm('Hapus Mitra?')"><i
                                                                class='bx bxs-trash'></i>Hapus</button>
                                                    </form>
                                                </li> --}}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="pagination pagination-sm justify-content-end pt-3">
                        {{ $petugas_kegiatan->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
