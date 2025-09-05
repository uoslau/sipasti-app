@if (session('showDownloadAlert'))
    <script>
        Swal.fire({
            title: 'Template berhasil diunggah!',
            text: "Silahkan unduh dengan menekan tombol Unduh dibawah ini.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Unduh',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('kegiatanob.download', $kegiatan->slug) }}";
            }
        });
    </script>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            @php
                $isGenerated = $kegiatan->is_generated;
                $badgeClass = $isGenerated ? 'success' : 'danger';
                $iconClass = $isGenerated ? 'bx bx-check-circle' : 'bx bx-x-circle';
                $titleText = $isGenerated ? 'sudah generate' : 'belum generate';
            @endphp
            <span class="badge rounded-pill bg-label-{{ $badgeClass }}">
                <i class="{{ $iconClass }}" data-bs-toggle="tooltip" title="{{ $titleText }}"></i>
            </span>
            <h5 class="mb-0">Edit Kegiatan {{ $kegiatan_updated_at }}</h5>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false">
                Aksi
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item {{ $kegiatan->is_generated ? 'disabled' : '' }}" href="javascript:void(0);"
                        onclick="confirmGenerate()">Generate</a>
                </li>
                <li>
                    <hr class="dropdown-divider" />
                </li>
                <li>
                    @if ($kegiatan->is_ob)
                        <a class="dropdown-item {{ $kegiatan->is_generated ? '' : 'disabled' }}" href="#"
                            data-bs-toggle="modal" data-bs-target="#unduhModal">
                            Unduh
                        </a>
                    @else
                        <a class="dropdown-item {{ $kegiatan->is_generated ? '' : 'disabled' }}"
                            href="{{ route('kegiatan.download', $kegiatan->slug) }}">Unduh
                        </a>
                    @endif
                </li>
            </ul>
        </div>
        <div class="modal fade" id="unduhModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unduhModalLabel">Unduh SPK / BAST Kegiatan OB</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('kegiatan.partials.unduh-ob')
                    </div>
                </div>
            </div>
        </div>
        <form id="generate-kontrak" action="{{ route('kontrak.generate', $kegiatan->slug) }}" method="POST"
            style="display: none;">
            @csrf
        </form>
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
                        <div class="input-group">
                            <div class="input-group-text">
                                <input type="hidden" name="is_ob" value="0">
                                <input class="form-check-input mt-0" type="checkbox" name="is_ob" value="1"
                                    aria-label="Checkbox for following text input"
                                    {{ old('is_ob', $kegiatan->is_ob) == '1' ? 'checked' : '' }} />
                            </div>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan"
                                placeholder="Gunakan Huruf Kapital Untuk Setiap Awal Kata" required autofocus
                                value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" />
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="defaultFormControlHelp" class="form-text text-primary">
                            [centang checkbox jika merupakan kegiatan O-B]
                        </div>
                    </div>
                </div>
            </div>
            {{-- tanggal mulai selesai, beban anggaran --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-6">
                        <label for="tangal_mulai" class="form-label">Tanggal Mulai</label>
                        <input class="form-control" type="date"
                            value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai) }}" id="tanggal_mulai"
                            name="tanggal_mulai" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-6">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input class="form-control" type="date"
                            value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai) }}" id="tanggal_selesai"
                            name="tanggal_selesai" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-6">
                        <label class="form-label" for="beban_anggaran">Beban Anggaran</label>
                        <input type="text" class="form-control @error('beban_anggaran') is-invalid @enderror"
                            id="beban_anggaran" name="beban_anggaran" placeholder="XXXX.XXX.XXX.005.A.521213" required
                            autofocus value="{{ old('beban_anggaran', $kegiatan->beban_anggaran) }}" />
                        @error('beban_anggaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="defaultFormControlHelp" class="form-text text-primary">
                            [contoh: 2903.BMA.009.005.A.521213]
                        </div>
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
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input class="form-control" type="text"
                                value="{{ old('honor_nias', number_format($kegiatan->honor_nias, 0, ',', '.')) }}"
                                id="honor_nias" name="honor_nias" placeholder="1.234.567.890"
                                oninput="formatRupiah(this)" />
                        </div>
                        <div id="defaultFormControlHelp" class="form-text text-primary">
                            [per 1 beban kerja]
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-6">
                        <label for="honor_nias_barat" class="form-label">Honor Nias Barat</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input class="form-control" type="text"
                                value="{{ old('honor_nias_barat', number_format($kegiatan->honor_nias_barat, 0, ',', '.')) }}"
                                id="honor_nias_barat" name="honor_nias_barat" placeholder="1.234.567.890"
                                oninput="formatRupiah(this)" />
                        </div>
                        <div id="defaultFormControlHelp" class="form-text text-primary">
                            [per 1 beban kerja]
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary ms-auto">Edit</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/confirm-generate.js') }}"></script>
