<div class="tab-content border-top">
    <form method="POST" action="{{ route('kegiatan.store') }}">
        @csrf
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
                                {{ old('is_ob') == '1' ? 'checked' : '' }} />
                        </div>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                            id="nama_kegiatan" name="nama_kegiatan"
                            placeholder="Gunakan Huruf Kapital Untuk Setiap Awal Kata" required autofocus
                            value="{{ old('nama_kegiatan') }}" aria-describedby="defaultFormControlHelp" />
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
        {{-- tanggal mulai selesai --}}
        <div class="row">
            <div class="col-md-6">
                <div class="mb-6">
                    <label for="tangal_mulai" class="form-label">Tanggal Mulai</label>
                    <input class="form-control" type="date" value="{{ old('tanggal_mulai') }}" id="tanggal_mulai"
                        name="tanggal_mulai" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-6">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                    <input class="form-control" type="date" value="{{ old('tanggal_selesai') }}" id="tanggal_selesai"
                        name="tanggal_selesai" />
                </div>
            </div>
        </div>
        {{-- beban anggaran, tim kerja --}}
        <div class="row">
            <div class="col-md-6">
                <div class="mb-6">
                    <label class="form-label" for="beban_anggaran">Beban Anggaran</label>
                    <input type="text" class="form-control @error('beban_anggaran') is-invalid @enderror"
                        id="beban_anggaran" name="beban_anggaran" placeholder="XXXX.XXX.XXX.005.A.521213" required
                        autofocus value="{{ old('beban_anggaran') }}" />
                    @error('beban_anggaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="defaultFormControlHelp" class="form-text text-primary">
                        [contoh: 2903.BMA.009.005.A.521213]
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-6">
                    <label for="tim_kerja_id" class="form-label">Tim Kerja</label>
                    <select class="form-select" id="tim_kerja_id" name="tim_kerja_id">
                        <option selected disabled>Pilih Tim Kerja</option>
                        @foreach ($tim_kerja as $tk)
                            @if ($tk->id !== 12)
                                <option value="{{ $tk->id }}"
                                    @if (old('tim_kerja_id') == $tk->id) selected
                        @elseif(!old('tim_kerja_id') && isset($user_tim_kerja_id) && $user_tim_kerja_id == $tk->id)
                            selected @endif>
                                    {{ $tk->nama_tim_kerja }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- honor nias, nias barat --}}
        <div class="row">
            <div class="col-md-6">
                <div class="mb-6">
                    <label for="honor_nias" class="form-label">Honor Nias</label>
                    <input class="form-control" type="text" value="{{ old('honor_nias') }}" id="honor_nias"
                        name="honor_nias" placeholder="1.234.567.890" oninput="formatRupiah(this)" />
                    <div id="defaultFormControlHelp" class="form-text text-primary">
                        [per satuan]
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-6">
                    <label for="honor_nias_barat" class="form-label">Honor Nias Barat</label>
                    <input class="form-control" type="text" value="{{ old('honor_nias_barat') }}"
                        id="honor_nias_barat" name="honor_nias_barat" placeholder="1.234.567.890"
                        oninput="formatRupiah(this)" />
                    <div id="defaultFormControlHelp" class="form-text text-primary">
                        [per satuan]
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
    </form>
</div>

<script src="{{ asset('js/kegiatan-form.js') }}"></script>
