<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Kegiatan {{ $kegiatan_updated_at }}</h5>
        <div class="demo-inline-spacing">
            <div class="btn-group">
                <button type="button" class="btn btn-primary">Generate</button>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="javascript:void(0);">Separated link</a></li>
                </ul>
            </div>
        </div>
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
                        <div id="defaultFormControlHelp" class="form-text">
                            (centang checkbox jika merupakan kegiatan O-B)
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
                            id="beban_anggaran" name="beban_anggaran" placeholder="contoh: 2903.BMA.009.005.A.521213"
                            required autofocus value="{{ old('beban_anggaran', $kegiatan->beban_anggaran) }}" />
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
                            id="honor_nias" name="honor_nias" placeholder="per satuan" oninput="formatRupiah(this)" />
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
                <button type="submit" class="btn btn-primary ms-auto">Edit</button>
            </div>
        </form>
    </div>
</div>
