<div class="col-xl-12">
    <div class="nav-align-top mb-6">
        <ul class="nav nav-pills mb-4 ms-auto" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-pills-top-bast" aria-controls="navs-pills-top-profile" aria-selected="true">
                    BAST
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-pills-top-spk" aria-controls="navs-pills-top-profile" aria-selected="false">
                    SPK
                </button>
            </li>
        </ul>
        <div class="tab-content border-top">
            {{-- bast --}}
            <div class="tab-pane fade show active" id="navs-pills-top-bast" role="tabpanel">
                <div class="col-12">
                    <h5 class="card-header text-center">Import Template BAST</h5>
                    <div class="card-body demo-vertical-spacing demo-only-element">
                        <form action="{{ route('kegiatanbast.upload', $kegiatan->slug) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $kegiatan->slug }}">
                            <div class="input-group">
                                <input type="file" required
                                    class="form-control @error('template_file') is-invalid @enderror" id="template_file"
                                    name="word_file" accept=".docx" />
                                @error('template_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button class="btn btn-primary" type="submit">Import</button>
                            </div>
                        </form>
                        <hr>

                        <h6>Daftar Placeholder yang Tersedia:</h6>
                        <p><small>Salin dan tempel placeholder berikut ke dalam file Word (.docx) Anda. Sistem akan
                                menggantinya secara otomatis saat dokumen diunduh.</small></p>
                        <table>
                            <tr>
                                <td><code>${nomor_kontrak}</code></td>
                                <td><code>: Nomor SPK</td>
                            </tr>
                            <tr>
                                <td><code>${nomor_bast}</code></td>
                                <td><code>: Nomor BAST</code></td>
                            </tr>
                            <tr>
                                <td><code>${nama_kegiatan}</code></td>
                                <td><code>: Nama Kegiatan</code></td>
                            </tr>
                            <tr>
                                <td><code>${nama_mitra}</code></td>
                                <td><code>: Nama Mitra</code></td>
                            </tr>
                            <tr>
                                <td><code>${beban_kerja}</code></td>
                                <td><code>: Volume / Beban Kerja</code></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            {{-- spk --}}
            <div class="tab-pane fade" id="navs-pills-top-spk" role="tabpanel">
                <div class="col-12">
                    <h5 class="card-header text-center">Import Template SPK</h5>
                    <div class="card-body demo-vertical-spacing demo-only-element">
                        <form action="{{ route('kegiatanspk.upload', $kegiatan->slug) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $kegiatan->slug }}">
                            <div class="input-group">
                                <input type="file" required
                                    class="form-control @error('template_file') is-invalid @enderror" id="template_file"
                                    name="word_file" accept=".docx" />
                                @error('template_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </form>
                        <hr>

                        <h6>Daftar Placeholder yang Tersedia:</h6>
                        <p><small>Salin dan tempel placeholder berikut ke dalam file Word (.docx) Anda. Sistem akan
                                menggantinya secara otomatis saat dokumen diunduh.</small></p>
                        <table>
                            <tr>
                                <td><code>${nomor_kontrak}</code></td>
                                <td><code>: Nomor SPK</code></td>
                            </tr>
                            <tr>
                                <td><code>${nomor_bast}</code></td>
                                <td><code>: Nomor BAST</code></td>
                            </tr>
                            <tr>
                                <td><code>${nama_kegiatan}</code></td>
                                <td><code>: Nama Kegiatan</code></td>
                            </tr>
                            <tr>
                                <td><code>${nama_mitra}</code></td>
                                <td><code>: Nama Mitra</code></td>
                            </tr>
                            <tr>
                                <td><code>${beban_kerja}</code></td>
                                <td><code>: Volume / Beban Kerja</code></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
