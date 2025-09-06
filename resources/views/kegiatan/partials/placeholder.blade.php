<div class="table-responsive text-nowrap">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th style="width: 30%;">Placeholder</th>
                <th style="width: 50%;">Deskripsi</th>
                <th class="text-center" style="width: 20%;">Aksi</th>
            </tr>
        </thead>
        {{-- Grup Info SPK & BAST --}}
        <tbody class="table-border-bottom-0">
            <tr>
                <td colspan="3" class="fw-bold bg-body-tertiary">
                    <i class='bx bxs-file-blank bx-xs me-1'></i> SPK & BAST
                </td>
            </tr>
            <tr>
                <td><code>${nomor_kontrak}</code></td>
                <td><code>Nomor SPK lengkap (Contoh: 001/1201_MITRA/2025)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${no_kontrak}</code></td>
                <td><code>Nomor SPK (Hanya Nomor)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${nomor_bast}</code></td>
                <td><code>Nomor BAST lengkap (Contoh: 001/1201_BAST/2025)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${no_bast}</code></td>
                <td><code>Nomor BAST (Hanya Nomor)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
        </tbody>

        {{-- Grup Info Mitra --}}
        <tbody class="table-border-bottom-0">
            <tr>
                <td colspan="3" class="fw-bold bg-body-tertiary">
                    <i class='bx bxs-user-detail bx-xs me-1'></i> Mitra
                </td>
            </tr>
            <tr>
                <td><code>${nama_mitra}</code></td>
                <td><code>Nama Lengkap Mitra</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${alamat}</code></td>
                <td><code>Alamat Mitra</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${pekerjaan}</code></td>
                <td><code>Pekerjaan Mitra</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
        </tbody>

        {{-- Grup Detail Tabel Kegiatan --}}
        <tbody class="table-border-bottom-0">
            <tr>
                <td colspan="3" class="fw-bold bg-body-tertiary">
                    <i class='bx bxs-spreadsheet bx-xs me-1'></i> Kegiatan
                </td>
            </tr>
            <tr>
                <td><code>${nama_kegiatan}</code></td>
                <td><code>Nama kegiatan</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${beban}</code></td>
                <td><code>Volume / Beban Kerja (Contoh: 10)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${satuan}</code></td>
                <td><code>Satuan Volume / Beban Kerja (Contoh: Dokumen)</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>${tim_kerja}</code></td>
                <td><code>Tim Kerja</code></td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-secondary btn-copy">
                        <i class='bx bx-copy bx-xs me-1'></i>Salin
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script src="{{ asset('js/copy-placeholder.js') }}"></script>
