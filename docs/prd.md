# PRD — SIPASTI

> Product Requirements Document aplikasi **SIPASTI** (Sistem Pengelolaan Administrasi Statistik Terpadu).
> Disusun dari hasil analisis langsung kode sumber (`app/`, `routes/`, `database/`, `resources/`).

---

## 1. Ringkasan Produk

| Aspek | Keterangan |
|---|---|
| Nama | **SIPASTI** |
| Pengguna | Internal **BPS Kabupaten Nias** |
| Domain wilayah | Kode wilayah `1201` (Nias) — aktif; `1225` (Nias Barat) — **dinonaktifkan** |
| Platform | Aplikasi web, **khusus desktop** (ada pembatas `d-xl-none`) |
| Stack | Laravel 12, PHP 8.2, MySQL, Blade + Bootstrap 5 |
| Tujuan utama | Mengelola kegiatan statistik, mitra/petugas lapangan, perhitungan honor, serta **pembuatan dokumen SPK & BAST otomatis** dari template Word |

SIPASTI menjembatani pekerjaan administrasi manual menjadi terotomatisasi: dari mencatat kegiatan survei,
menugaskan mitra beserta beban kerjanya, menghitung honor sesuai tarif wilayah, sampai **men-generate
nomor kontrak (SPK) & nomor BAST** dan mengunduhnya sebagai dokumen Word siap tanda tangan.

---

## 2. Latar Belakang & Masalah

Sebelumnya, penomoran kontrak dan pembuatan dokumen SPK/BAST dilakukan manual: menghitung honor per
petugas, menomori kontrak secara berurutan, menyalin data ke template Word satu per satu. Proses ini
lambat, rawan salah hitung, dan sulit di-rekap per bulan.

SIPASTI menyelesaikan masalah tersebut dengan:

1. **Satu sumber data** untuk kegiatan, mitra, dan penugasan.
2. **Perhitungan honor otomatis** berbasis tarif wilayah × beban kerja.
3. **Penomoran otomatis** SPK & BAST yang unik dan konsisten (1 petugas = 1 kontrak per bulan).
4. **Generate dokumen Word massal** (SPK/BAST/OB) yang dikemas dalam ZIP.
5. **Rekap bulanan** honor per petugas beserta batas honor maksimum (deteksi kelebihan bayar).

---

## 3. Aktor & Hak Akses

| Aktor | Identifikasi | Hak akses |
|---|---|---|
| **Admin** | Kolom `users.is_admin = true` | Akses **penuh** ke semua fitur & semua tim kerja (`Gate::before`). Mengelola user. |
| **User** | `is_admin = false`, terhubung ke satu/lebih **tim kerja** (`tim_kerja_user`) | Melihat semua kegiatan lintas tim, tetapi **hanya boleh membuat/mengubah/menghapus kegiatan milik tim kerjanya**. |
| **Anggota tim kerja `PEMEJA`** | `alias_tim_kerja = 'PEMEJA'` | Tambahan akses modul **Sensus Ekonomi** (selain admin). |

**Aturan otorisasi kunci:**

- **Baca terbuka, tulis terbatas** — semua user boleh *melihat* kegiatan tim lain (`view = true`), tetapi
  `update`/`delete` hanya untuk anggota tim kerja pemilik kegiatan (`KegiatanPolicy::owns`).
- User tanpa tim kerja **tidak boleh membuat** kegiatan (`create` butuh minimal 1 tim kerja).
- Modul **Manajemen User** hanya admin (middleware `admin`).

---

## 4. Fitur / Modul

### 4.1 Autentikasi
- Login memakai **username** + password (bukan email).
- Rate limiting: **5 percobaan/akun+IP per 60 detik** dan **20 percobaan/IP per 300 detik**.
- Logout meng-invalidate session. Session login diregenerasi saat sukses.
- Tidak ada registrasi mandiri, verifikasi email, atau reset password via email.

### 4.2 Dashboard
- Kartu statistik: jumlah kegiatan bulan acuan (+perbandingan bulan sebelumnya), total honor bulan acuan,
  jumlah mitra unik, kegiatan belum di-generate.
- Grafik (ApexCharts): tren kegiatan per bulan (tahun berjalan vs tahun lalu), komposisi status
  SPK/BAST (sudah/belum digenerate), sebaran kegiatan per tim kerja, sebaran petugas per wilayah.
- Daftar 5 kegiatan belum digenerate + 6 kegiatan terbaru.
- **Scoping per tim kerja**: admin melihat semua, user melihat tim kerjanya saja.
- "Bulan acuan" = bulan berjalan bila ada data, jika tidak bulan terakhir yang berdata.

### 4.3 Kegiatan (BAST)
- Daftar kegiatan dengan pencarian (nama kegiatan / alias tim kerja) + paginasi 10.
- Tambah/edit kegiatan (nama, status O-B, tanggal mulai/selesai, beban anggaran, tim kerja, honor Nias),
  serta total honor agregat per kegiatan.
- Hapus kegiatan (soft delete, otomatis menghapus petugas terkait).
- Halaman edit memuat daftar petugas + aksi generate & unduh dokumen.

### 4.4 Petugas Kegiatan (Mitra pada Kegiatan)
- Autocomplete mitra (maks 5) — **hanya mitra wilayah Nias**.
- Tambah/edit/hapus petugas pada kegiatan (bertugas sebagai, beban kerja, satuan beban kerja).
- Validasi: nama jabatan/satuan hanya huruf, beban kerja integer ≥ 1, cegah duplikasi NIK dalam kegiatan.
- **Cek bentrok kegiatan O-B**: mitra yang sudah terdaftar pada kegiatan O-B di bulan yang sama akan
  diperingatkan (bisa dilewati dengan konfirmasi `bypass_ob_check`).
- **Import Excel** petugas baru dan **update** data petugas (`xlsx/xls/csv`, maks 2 MB).

### 4.5 Nomor Kontrak (Generate SPK & BAST)
- Men-generate **nomor kontrak** (unik per tahun; 1 petugas hanya punya 1 kontrak per bulan — nomor
  dipakai ulang bila petugas sudah punya kontrak di bulan yang sama) dan **nomor BAST** (selalu increment).
- Menandai kegiatan `is_generated = true`.
- Menolak generate ulang bila sudah pernah digenerate, atau bila kegiatan belum punya petugas.

### 4.6 Kontrak / SPK (Rekap Bulanan)
- Rekap petugas per bulan/tahun: total honor, batas honor maksimum, sisa honor, dan penanda kelebihan.
- Batas honor ditentukan dari **posisi mitra** (`Mitra Pendataan` → honor pendataan, `Mitra Pengolahan`
  → honor pengolahan, `Mitra (Pendataan dan Pengolahan)` → tergantung keikutsertaan tim kerja id `1`).
- Status kegiatan per petugas (pending/ongoing/completed) dengan ikon.
- Pilih petugas (checkbox massal) → unduh SPK.

### 4.7 Download Dokumen (Word)
- **BAST** per mitra (ZIP) dari template `template_bast.docx` — syarat: sudah digenerate.
- **SPK** per mitra (ZIP) dari template `template_kontrak.docx` — petugas O-B tidak dibuatkan SPK,
  semua kegiatan harus sudah digenerate, honor dibayar di-cap pada honor maksimum.
- **OB**: upload template khusus per kegiatan lalu unduh dokumennya.
- Unduh template langsung via `file.download`.

### 4.8 Mitra
- Daftar mitra + filter tahun, dengan matriks jumlah kegiatan per bulan (Jan–Des) + total.
- **Export Excel** rekap mitra per tahun (`Rekap_Mitra_Tahun_{tahun}.xlsx`).
- CATATAN: CRUD mitra (create/store/show/edit/update/destroy) **belum diimplementasikan** (masih stub).

### 4.9 Manajemen User (Admin)
- Daftar user + filter (pencarian, role, tim kerja) + statistik (total, admin, tanpa tim, tim terpakai).
- CRUD user + sinkronisasi relasi tim kerja.
- Reset password user oleh admin.
- Proteksi: admin tidak bisa menurunkan status admin dirinya sendiri; tidak bisa menghapus akun sendiri;
  tidak bisa menghapus admin terakhir.

### 4.10 Profil
- Lihat/ubah data akun sendiri (nama, username, email).
- Ubah password (wajib `current_password`, min 6, konfirmasi).

### 4.11 Sensus Ekonomi *(SEMENTARA — dapat dihapus)*
- Dashboard data sensus ekonomi hasil import Excel + pencarian AJAX.
- Filter bertingkat (cascading): **Kecamatan → Desa (level 4) → SLS (level 5) → Pencacah (email)**;
  setiap tingkat hanya menampilkan nilai yang ada di bawah pilihan tingkat atasnya.
- Import Excel sinkron (hapus data lama + import dalam satu transaksi, mendukung ribuan baris).
- Kosongkan data.
- Akses: admin atau tim kerja `PEMEJA`.
- Fitur ini **berdiri sendiri** (model, controller, view, import, CSS/JS, route terpisah) agar bisa
  dihapus bersih tanpa memengaruhi modul lain. Instruksi penghapusan ada di komentar filenya.

---

## 5. Aturan Bisnis

| # | Aturan | Sumber |
|---|---|---|
| 1 | Tanggal mulai & selesai kegiatan harus dalam **bulan dan tahun yang sama**. | `KegiatanController` |
| 2 | Nama kegiatan tidak boleh mengandung `/` atau `\`. | `KegiatanController` |
| 3 | Hanya wilayah **Nias (`1201`)** yang aktif; mitra Nias Barat (`1225`) ditolak saat ditambahkan. | `PetugasKegiatanController::store` |
| 4 | Honor = `honor_nias × beban_kerja`; bila kegiatan **O-B** honornya flat `honor_nias`. | `PetugasKegiatanController` |
| 5 | 1 petugas hanya boleh punya **1 nomor kontrak per bulan**; nomor BAST selalu unik/increment. | `NomorKontrakController` |
| 6 | Mitra hanya boleh ikut 1 kegiatan O-B per bulan (kecuali di-bypass admin). | `PetugasKegiatanController::store` |
| 7 | Pitаh honor dibatasi `honor_max` sesuai posisi mitra; kelebihan ditandai. | `KontrakController`, `DownloadController` |
| 8 | Petugas yang terlibat kegiatan O-B **tidak dibuatkan SPK**. | `DownloadController::downloadSPK` |
| 9 | Perubahan bulan kegiatan yang sudah digenerate akan menghapus & generate ulang nomor. | `KegiatanController::update` |
| 10 | Non-admin hanya boleh menambah/memindahkan kegiatan ke tim kerja yang diikutinya. | `KegiatanController` |

---

## 6. Ruang Lingkup

### Sudah tersedia
Dashboard, manajemen kegiatan, penugasan petugas (termasuk import Excel), generate nomor SPK/BAST,
rekap kontrak bulanan, generate & unduh dokumen Word (BAST/SPK/OB), daftar & export mitra,
manajemen user, profil, dan modul Sensus Ekonomi (sementara).

### Belum / utang teknis (di luar cakupan saat ini)
- CRUD **Mitra** (create/store/show/edit/update/destroy) masih kosong — hanya index + export.
- `MitraPolicy` & `PetugasKegiatanPolicy` masih stub (`false`); otorisasi petugas dilakukan manual di controller.
- `StoreMitraRequest`/`UpdateMitraRequest::authorize()` bernilai `false`.
- `APP_NAME` masih default `"Laravel"` (tampilan memakai teks "SIPASTI" hardcode).
- Logika honor **Nias Barat** masih tersisa di `PetugasKegiatanController::update` & `KontrakController`
  meskipun pendaftaran Nias Barat dinonaktifkan.

---

## 7. Metrik Keberhasilan (indikatif)

- Waktu pembuatan dokumen SPK/BAST per bulan turun drastis dibanding cara manual.
- Tidak ada lagi nomor kontrak/BAST ganda atau honor kelebihan bayar yang lolos.
- Rekap bulanan honor tersedia real-time tanpa rekap manual.

---

## 8. Referensi

- `routes/web.php`, `bootstrap/app.php`
- `app/Http/Controllers/*`
- `app/Policies/KegiatanPolicy.php`, `app/Providers/AppServiceProvider.php`
- `database/migrations/*`, `database/seeders/*`
- Dokumentasi terkait: [`architecture.md`](./architecture.md), [`schema.md`](./schema.md),
  [`routes.md`](./routes.md), [`ui-guidelines.md`](./ui-guidelines.md)
