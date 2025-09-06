<div class="col-xl-12">
    <div class="nav-align-top mb-6">
        <ul class="nav nav-pills mb-4 ms-auto" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-pills-top-placeholder" aria-controls="navs-pills-top-profile"
                    aria-selected="true">
                    Placeholder
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-pills-top-bast" aria-controls="navs-pills-top-profile" aria-selected="true">
                    Import
                </button>
            </li>
        </ul>
        <div class="tab-content border-top">
            {{-- placeholder --}}
            <div class="tab-pane fade show active" id="navs-pills-top-placeholder" role="tabpanel">
                <div class="col-12">
                    <h6>Daftar Placeholder yang Tersedia:</h6>
                    <p><small>Salin dan tempel placeholder berikut ke dalam file Word (.docx) Anda. Sistem akan
                            menggantinya secara otomatis saat dokumen diunduh.</small></p>
                    @include('kegiatan.partials.placeholder')
                </div>
            </div>
            {{-- import --}}
            <div class="tab-pane fade show" id="navs-pills-top-bast" role="tabpanel">
                <div class="col-12">
                    <h5 class="card-header text-center">Import Template Kegiatan OB</h5>
                    <div class="card-body demo-vertical-spacing demo-only-element">
                        <form action="{{ route('kegiatanob.upload', $kegiatan->slug) }}" method="POST"
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
                            <div id="defaultFormControlHelp" class="form-text text-primary">
                                [Pastikan placeholder sudah disalin di template yang akan digunakan.]
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
