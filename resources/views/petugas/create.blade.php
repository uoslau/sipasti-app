<div class="col-xl-12">
    <div class="nav-align-top mb-6">
        <ul class="nav nav-pills mb-4 ms-auto" role="tablist">
            @php
                $isGenerated = $kegiatan->is_generated;
            @endphp
            <li class="nav-item">
                <button type="button" class="nav-link {{ $isGenerated ? '' : 'active' }}" role="tab"
                    data-bs-toggle="tab" data-bs-target="#navs-pills-top-form" aria-controls="navs-pills-top-home"
                    aria-selected="{{ $isGenerated ? 'false' : 'true' }}" {{ $isGenerated ? 'disabled' : '' }}>
                    Form
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                    data-bs-target="#navs-pills-top-import" aria-controls="navs-pills-top-profile" aria-selected="false"
                    {{ $isGenerated ? 'disabled' : '' }}>
                    Import
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link {{ $isGenerated ? 'active' : '' }}" role="tab"
                    data-bs-toggle="tab" data-bs-target="#navs-pills-top-update" aria-controls="navs-pills-top-profile"
                    aria-selected="{{ $isGenerated ? 'true' : 'false' }}">
                    Update
                </button>
            </li>
        </ul>
        <div class="tab-content border-top">
            {{-- form --}}
            <div class="tab-pane fade {{ $isGenerated ? '' : 'show active' }}" id="navs-pills-top-form" role="tabpanel">
                <form method="POST" id="form-tambah-petugas" action="{{ route('petugas.store', $kegiatan->slug) }}">
                    @csrf
                    {{-- nama mitra --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-6 position-relative">
                                <label class="form-label" for="nama_mitra">Nama Mitra</label>
                                <input type="search" class="form-control @error('nama_mitra') is-invalid @enderror"
                                    id="nama_mitra" name="nama_mitra" placeholder="Ketik Untuk Mencari" required
                                    autofocus value="{{ old('nama_mitra') }}" autocomplete="off" />
                                <ul id="mitra-list" class="list-group position-absolute w-100"></ul>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="nik" name="nik" value="{{ old('nik') }}">
                    {{-- bertugas sebagai, wilayah tugas --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-6">
                                <label class="form-label" for="bertugas_sebagai">Bertugas Sebagai</label>
                                <input type="text"
                                    class="form-control @error('bertugas_sebagai') is-invalid @enderror"
                                    id="bertugas_sebagai" name="bertugas_sebagai"
                                    placeholder="Misalnya PCL, PML, Operator Entri, dll" required autofocus
                                    value="{{ old('bertugas_sebagai') }}" />
                                @error('bertugas_sebagai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6">
                                <label for="wilayah_tugas" class="form-label">Wilayah Tugas</label>
                                <input class="form-control" type="text" value="{{ old('wilayah_id') }}"
                                    id="wilayah_tugas" name="wilayah_tugas" placeholder="Otomatis Terisi" disabled />
                            </div>
                        </div>
                    </div>
                    {{-- beban kerja, satuan beban kerja --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-6">
                                <label for="beban_kerja" class="form-label">Beban Kerja</label>
                                <input class="form-control" type="number" value="{{ old('beban_kerja') }}"
                                    id="beban_kerja" name="beban_kerja" placeholder="Jumlah Beban Kerja" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6">
                                <label class="form-label" for="satuan_beban_kerja">Satuan Beban Kerja</label>
                                <input type="text"
                                    class="form-control @error('satuan_beban_kerja') is-invalid @enderror"
                                    id="satuan_beban_kerja" name="satuan_beban_kerja"
                                    placeholder="Misalnya Dokumen, Segmen, dll" required autofocus
                                    value="{{ old('satuan_beban_kerja') }}" />
                                @error('satuan_beban_kerja')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
            {{-- import --}}
            <div class="tab-pane fade" id="navs-pills-top-import" role="tabpanel">
                <div class="col-12">
                    <h5 class="text-center">Import via File Excel Petugas</h5>
                    <div class="card-body demo-vertical-spacing demo-only-element">
                        <form action="{{ route('petugas.import', $kegiatan->slug) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $kegiatan->slug }}">
                            <div class="input-group">
                                <input type="file" required class="form-control" id="file"
                                    name="excel_file" />
                                <button class="btn btn-primary" type="submit">Import</button>
                            </div>
                        </form>
                    </div>
                    <div class="text-center">
                        Template import file excel dapat diunduh <a
                            href="{{ route('file.download', ['nama_file' => 'petugas_import_excel.xlsx']) }}">disini</a>
                    </div>
                </div>
            </div>
            {{-- update --}}
            <div class="tab-pane fade {{ $isGenerated ? 'show active' : '' }}" id="navs-pills-top-update"
                role="tabpanel">
                <div class="col-12">
                    <h5 class="card-header text-center">Update via File Excel Petugas</h5>
                    <div class="card-body demo-vertical-spacing demo-only-element">
                        <form action="{{ route('petugas.import_update', $kegiatan->slug) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $kegiatan->slug }}">
                            <div class="input-group">
                                <input type="file" required class="form-control" id="file"
                                    name="excel_file" />
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                            <div id="defaultFormControlHelp" class="form-text text-primary">
                                [pastikan seluruh petugas yang akan diupdate sudah terdaftar di kegiatan ini]
                            </div>
                        </form>
                    </div>
                    <div class="text-center">
                        Template import file excel dapat diunduh <a
                            href="{{ route('file.download', ['nama_file' => 'petugas_import_excel.xlsx']) }}">disini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #mitra-list {
        background-color: white;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
    }

    #mitra-list .list-group-item {
        cursor: pointer;
    }

    #mitra-list .list-group-item:hover {
        background-color: #f0f0f0;
    }

    #mitra-list .active {
        background-color: #696cff;
        color: white;
    }
</style>

<script>
    let activeIndex = -1;
    let results = [];

    document.getElementById('nama_mitra').addEventListener('input', function() {
        let query = this.value;
        activeIndex = -1;
        if (query.length > 2) {
            fetch(`{{ route('petugas.search') }}?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    results = data;
                    let list = document.getElementById('mitra-list');
                    list.innerHTML = '';
                    data.forEach((item, index) => {
                        let li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';

                        let formattedName = item.nama_mitra
                            .toLowerCase()
                            .split(' ')
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(' ');

                        li.textContent = formattedName;
                        li.dataset.index = index;
                        li.addEventListener('click', function() {
                            selectMitra(item);
                        });
                        list.appendChild(li);
                    });
                });
        }
    });

    document.getElementById('nama_mitra').addEventListener('keydown', function(e) {
        let list = document.getElementById('mitra-list');
        if (list.childNodes.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % results.length;
                updateHighlight(list);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + results.length) % results.length;
                updateHighlight(list);
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < results.length) {
                    selectMitra(results[activeIndex]);
                }
            }
        }
    });

    function updateHighlight(list) {
        Array.from(list.childNodes).forEach((node, index) => {
            if (index === activeIndex) {
                node.classList.add('active');
            } else {
                node.classList.remove('active');
            }
        });
    }

    function selectMitra(item) {
        let formattedName = item.nama_mitra
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');

        document.getElementById('nama_mitra').value = formattedName;
        document.getElementById('nik').value = item.nik;
        document.getElementById('wilayah_tugas').value = item.wilayah_tugas.nama_wilayah;
        document.getElementById('mitra-list').innerHTML = '';
        activeIndex = -1;
    }
</script>
