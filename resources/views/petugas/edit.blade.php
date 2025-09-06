{{-- @dd($petugas_kegiatan) --}}
<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Petugas</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST"
                            action="{{ route('petugas.update', [$kegiatan->slug, $petugas_kegiatan->nik]) }}">
                            @csrf
                            @method('PUT')
                            {{-- nama petugas --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-6">
                                        <label class="form-label" for="nama_mitra">Nama Mitra</label>
                                        <input type="text"
                                            class="form-control @error('nama_mitra') is-invalid @enderror"
                                            id="nama_mitra" name="nama_mitra" placeholder="Ketik Untuk Mencari" required
                                            autofocus
                                            value="{{ old('nama_mitra', $petugas_kegiatan->mitra->nama_mitra) }}"
                                            disabled readonly />
                                        @error('nama_mitra')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            {{-- bertugas_sebagai, wilayah_tugas --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-6">
                                        <label class="form-label" for="bertugas_sebagai">Bertugas Sebagai</label>
                                        <input type="text"
                                            class="form-control @error('bertugas_sebagai') is-invalid @enderror"
                                            id="bertugas_sebagai" name="bertugas_sebagai"
                                            placeholder="Misalnya PCL, PML, Operator Entri, dll" required autofocus
                                            value="{{ old('bertugas_sebagai', $petugas_kegiatan->bertugas_sebagai) }}" />
                                        @error('bertugas_sebagai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-6">
                                        <label for="wilayah_tugas" class="form-label">Wilayah Tugas</label>
                                        <input class="form-control" type="text" id="wilayah_tugas"
                                            name="wilayah_tugas" placeholder="Otomatis Terisi"
                                            value="{{ old('nama_wilayah', $petugas_kegiatan->mitra->wilayahTugas->nama_wilayah) }}"
                                            disabled readonly />
                                    </div>
                                </div>
                            </div>
                            {{-- beban kerja --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-6">
                                        <label for="beban_kerja" class="form-label">Beban Kerja</label>
                                        <input class="form-control" type="number"
                                            value="{{ old('beban_kerja', $petugas_kegiatan->beban_kerja) }}"
                                            id="beban_kerja" name="beban_kerja" placeholder="Beban Kerja" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-6">
                                        <label class="form-label" for="satuan_beban_kerja">Satuan Beban Kerja</label>
                                        <input type="text"
                                            class="form-control @error('satuan_beban_kerja') is-invalid @enderror"
                                            id="satuan_beban_kerja" name="satuan_beban_kerja"
                                            placeholder="Misalnya Dokumen, Segmen, Rumah Tangga, dll" required autofocus
                                            value="{{ old('satuan_beban_kerja', $petugas_kegiatan->satuan_beban_kerja) }}" />
                                        @error('satuan_beban_kerja')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary ms-auto">Edit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
