{{--
    SEMENTARA — fitur Sensus Ekonomi.
    Dipakai di dua tempat: kartu utama saat data masih kosong, dan modal "Import ulang".
--}}
<form action="{{ route('sensus-ekonomi.import') }}" method="POST" enctype="multipart/form-data" data-sensus-import>
    @csrf

    <label class="sensus-dropzone" for="sensus-file" data-sensus-dropzone>
        <i class="bx bx-cloud-upload" aria-hidden="true"></i>
        <span class="sensus-dropzone-title">Tarik file ke sini, atau klik untuk memilih</span>
        <span class="sensus-dropzone-hint">Format .xlsx, .xls, atau .csv &middot; maksimal
            {{ $uploadLimit ?? '20 MB' }}</span>
        <input type="file" id="sensus-file" name="excel_file" accept=".xlsx,.xls,.csv,.txt"
            class="visually-hidden" data-sensus-file required />
    </label>

    <div class="sensus-file-name d-none" data-sensus-file-name></div>

    <details class="sensus-columns">
        <summary>Kolom yang harus ada di baris pertama file ({{ count(\App\Imports\SensusEkonomiImport::COLUMNS) }}
            kolom)</summary>
        <ul>
            @foreach (\App\Imports\SensusEkonomiImport::COLUMNS as $column)
                <li><code>{{ $column }}</code></li>
            @endforeach
        </ul>
    </details>

    <div class="alert alert-warning d-flex align-items-start gap-2 py-2 px-3 mt-3 mb-0 small">
        <i class="bx bx-info-circle mt-1" aria-hidden="true"></i>
        <span>Import akan mengganti seluruh data sensus ekonomi yang saat ini tersimpan. Untuk file berisi ribuan
            baris, prosesnya bisa berjalan beberapa saat — jangan tutup halaman sebelum selesai.</span>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary" data-sensus-submit>
            <i class="bx bx-upload me-1" aria-hidden="true"></i><span data-sensus-submit-label>Import data</span>
        </button>
    </div>
</form>
