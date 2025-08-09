{{-- @dd($petugas_kegiatan) --}}
<x-layout>
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
    @if (session()->has('error'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: "{{ session('error') }}",
                confirmButtonColor: '#696cff',
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menambahkan Petugas',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#696cff',
            });
        </script>
    @endif
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Kegiatan - [Terakhir Diupdate : {{ $updated_at }}]</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('kegiatan.update', [$kegiatan->slug]) }}">
                            @csrf
                            @method('PUT')
                            {{-- nama kegiatan --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-6">
                                        <label class="form-label" for="nama_kegiatan">Nama Kegiatan</label>
                                        <input type="text"
                                            class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                            id="nama_kegiatan" name="nama_kegiatan"
                                            placeholder="Gunakan Huruf Kapital Untuk Setiap Awal Kata" required
                                            autofocus value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" />
                                        @error('nama_kegiatan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            {{-- tanggal mulai selesai, beban anggaran --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label for="tangal_mulai" class="form-label">Tanggal Mulai</label>
                                        <input class="form-control" type="date"
                                            value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai) }}"
                                            id="tanggal_mulai" name="tanggal_mulai" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                        <input class="form-control" type="date"
                                            value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai) }}"
                                            id="tanggal_selesai" name="tanggal_selesai" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label class="form-label" for="beban_anggaran">Beban Anggaran</label>
                                        <input type="text"
                                            class="form-control @error('beban_anggaran') is-invalid @enderror"
                                            id="beban_anggaran" name="beban_anggaran"
                                            placeholder="contoh: 2903.BMA.009.005.A.521213" required autofocus
                                            value="{{ old('beban_anggaran', $kegiatan->beban_anggaran) }}" />
                                        @error('beban_anggaran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            {{-- tim kerjam, honor nias nias barat --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label for="tim_kerja_id" class="form-label">Tim Kerja</label>
                                        <select class="form-select" id="tim_kerja_id" name="tim_kerja_id">
                                            <option selected disabled>Pilih Tim Kerja</option>
                                            @foreach ($tim_kerja as $tk)
                                                @if ($tk->id !== 12)
                                                    @if (old('tim_kerja_id', $kegiatan->tim_kerja_id) == $tk->id)
                                                        <option value="{{ $tk->id }}" selected>
                                                            {{ $tk->nama_tim_kerja }}
                                                        </option>
                                                    @else
                                                        <option value="{{ $tk->id }}">
                                                            {{ $tk->nama_tim_kerja }}
                                                        </option>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label for="honor_nias" class="form-label">Honor Nias</label>
                                        <input class="form-control" type="text"
                                            value="{{ old('honor_nias', number_format($kegiatan->honor_nias, 0, ',', '.')) }}"
                                            id="honor_nias" name="honor_nias" placeholder="per satuan"
                                            oninput="formatRupiah(this)" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-6">
                                        <label for="honor_nias_barat" class="form-label">Honor Nias Barat</label>
                                        <input class="form-control" type="text"
                                            value="{{ old('honor_nias_barat', number_format($kegiatan->honor_nias_barat, 0, ',', '.')) }}"
                                            id="honor_nias_barat" name="honor_nias_barat" placeholder="per satuan"
                                            oninput="formatRupiah(this)" />
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary ms-auto">Edit Kegiatan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- tabel detail petugas --}}
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0 me-2">Detail Petugas</h5>
                <a href="#" class="btn btn-primary ms-auto" data-bs-toggle="modal"
                    data-bs-target="#addPetugasModal">
                    + Petugas
                </a>
                <div class="modal fade" id="addPetugasModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addPetugasModalLabel">Tambah
                                    Petugas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @include('petugas.create', ['slug' => $kegiatan->slug])
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
                                        <span class="badge bg-label-danger">
                                            {{ $p->bertugas_sebagai }} / {{ $p->beban_kerja }}
                                            {{ $p->satuan_beban_kerja }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary">
                                            {{ $p->wilayah_tugas == '1201' ? 'Nias' : 'Nias Barat' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-success">
                                            {{ formatNominal($p->honor) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('petugas.edit', [$kegiatan->slug, $p->nik]) }}">Edit</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider" />
                                                </li>
                                                <li>
                                                    <form
                                                        action="{{ route('petugas.destroy', [$kegiatan->slug, $p->nik]) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="dropdown-item btn-delete-petugas">Hapus</button>
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
                        {{ $petugas_kegiatan->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>

<script>
    function formatRupiah(input) {
        let value = input.value.replace(/\./g, '');
        if (!isNaN(value)) {
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        } else {
            input.value = value.slice(0, -1);
        }
    }
</script>
