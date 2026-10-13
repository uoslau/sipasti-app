# UI Guidelines — SIPASTI

> Panduan tampilan & frontend SIPASTI. Disusun dari kode sumber di `resources/views/`, `public/css/`,
> `public/js/`, dan aset template.

---

## 0. Peringatan Utama (baca dulu)

1. **Gunakan Blade + Bootstrap 5 (template Sneat), BUKAN Tailwind.**
   Tailwind + Vite memang terpasang (`package.json`, `vite.config.js`, `resources/css/app.css`), tetapi
   **tidak ada satu pun view yang memanggil `@vite(...)`** — jadi Tailwind praktis *dormant*/tidak dipakai.
   Jangan menambahkan kelas Tailwind: tidak ada mekanisme yang memuatnya ke halaman.
2. **Halaman adalah `Blade` murni** dengan aset statis dari `public/assets/` + CSS kustom di `public/css/`.
3. **Aplikasi khusus desktop.** Ada overlay penutup untuk layar `< xl` (lihat §2).

---

## 1. Stack Frontend

| Aspek | Kenyataan | Sumber |
|---|---|---|
| Rendering | Blade (server-side) | `resources/views/**` |
| CSS framework | Bootstrap 5 (template Sneat) | `public/assets/vendor/css/core.css`, `theme-default.css` |
| CSS kustom | Ya, di `public/css/` | `dashboard.css`, `users.css`, `sensus-ekonomi.css`, `index.spk.css` |
| JS runtime | jQuery, Bootstrap bundle, Popper, perfect-scrollbar | `components/layout.blade.php` |
| Ikon | **Boxicons** (`bx`, `bx-*`, `bxs-*`) | `assets/vendor/fonts/boxicons.css` |
| Chart | **ApexCharts** | `assets/vendor/libs/apex-charts/*`, `public/js/dashboard.js` |
| Notifikasi | **SweetAlert2** via CDN | `layout.blade.php`, `components/alerts.blade.php` |
| Font | **Public Sans** (Google Fonts) | `layout.blade.php` |
| Build tool | Vite (input `resources/css/app.css` + `resources/js/app.js`) | `vite.config.js` |

---

## 2. Layout & Struktur Halaman

### Komponen utama
Layout dipakai sebagai `<x-layout>...</x-layout>` (anonymous Blade component, tanpa class `App\View\Components`).

```
<aside> <x-sidebar />                      ← navigasi samping
<div class="layout-page">
    {{-- <x-navbar /> DINONAKTIFKAN --}}
    <div class="content-wrapper">
        {{ $slot }}                        ← konten halaman
        <x-alerts />                       ← flash message SweetAlert2
        <x-footer />                       ← footer
```

Atribut tema di `<html>`: `class="light-style layout-menu-fixed layout-compact"`, `data-theme="theme-default"`,
`data-style="light"`.

### Guard "khusus desktop"
`layout.blade.php` menyisipkan overlay `d-flex d-xl-none position-fixed ...` berisi gambar + pesan
**"Website Khusus Desktop"**. Ini konvensi penting: perancangan mengasumsikan layar lebar.

### Struktur konten standar
```blade
<x-layout>
  <div class="container-xxl flex-grow-1 container-p-y [prefix]-page">
     header halaman (judul <h4> + deskripsi <p class="text-muted"> + aksi)
     [kartu ringkasan statistik]         ← opsional
     card utama (toolbar filter → tabel/form → pagination)
  </div>
</x-layout>

<script src="{{ asset('js/xxx.js') }}"></script>   ← JS halaman DIDAFTARKAN DI LUAR </x-layout>
```

### Sidebar (`components/sidebar.blade.php`)
- Brand "sipasti" (logo SVG inline `#696cff`).
- Menu dikelompokkan dengan `menu-header` (uppercase kecil): **BAST & SPK**, **Mitra**, **Sensus Ekonomi**, **Akun**.
- Item: Dashboard, Kegiatan/BAST, Kontrak/SPK, Mitra, Sensus Ekonomi *(badge "sementara")*, Manajemen User (admin),
  Profil, Log out.
- **State aktif** via `request()->routeIs(...)` / `request()->is('kegiatan*')` → tambah class `active`.
- Item bergantung izin: `@if (EnsureSensusEkonomiAccess::allows(auth()->user()))`, `@if (auth()->user()->is_admin)`.
- Logout = form POST tersembunyi `#logout-form` (dipicu `onclick`).

### Navbar & Footer
- `components/navbar.blade.php` masih versi template (search, dropdown "John Doe") dan **sengaja dinonaktifkan**.
- Footer: `© 2024, made with ☕ by BPS Kabupaten Nias`.

---

## 3. Design System

### Warna
- **Primary brand: `#696cff`.**
- Warna semantik template: success `#71dd37`, warning `#ffab00`, danger, info.
- **Design token per-halaman** (CSS variable, di-scope dengan prefix kelas):
  ```css
  .users-page    { --users-primary: #696cff; --users-heading: #384551; --users-muted: #8592a3;
                   --users-line: #e4e6eb; --users-surface: #f5f6f8; }
  .dash-page     { --dash-... }
  .sensus-page   { --sensus-... }
  ```
  Pakai pola ini untuk halaman baru agar tidak bentrok dengan halaman lain.

### Badge & label (pola utama)
- Badge lembut Sneat: `badge bg-label-primary|success|warning|danger|info|secondary`.
- Status generate: `badge rounded-pill bg-label-success/danger` + ikon `bx bx-check-circle` / `bx bx-x-circle`
  + `data-bs-toggle="tooltip"`.
- Role: `bg-label-success` (Admin, `bxs-shield-alt-2`) / `bg-label-secondary` (User).
- Chip netral: class `.dash-chip` / `.users-chip` (pill abu, `background: var(--surface)`).

### Tombol
| Peran | Class |
|---|---|
| Utama | `btn btn-primary` (sering + ikon `<i class="bx bx-plus me-1">`) |
| Sekunder/batal | `btn btn-outline-secondary` |
| Peringatan | `btn btn-warning` |
| Danger | `btn btn-outline-danger` |
| Teks | `btn btn-text-secondary` |
| Kecil | `btn btn-xs btn-outline-primary` |
| Grup aksi | `btn-group` + `dropdown-toggle` |

### Card
`<div class="card">` → `card-header` (judul `<h5 class="mb-0">` + `<small class="text-muted">`) →
`card-body` / `card-footer`. Kartu statistik: `card h-100` di dalam `col-sm-6 col-xl-3`, ikon bulat `bg-label-*`.

### Tabel
- Wrapper `<div class="table-responsive text-nowrap">`, tabel `table table-hover`.
- Lebar kolom sering fixed: `style="table-layout: fixed; width: 100%;"` + `<th style="width: X%">`.
- Header uppercase kecil (lihat `.dash-table thead th`, `.users-table thead th`).
- Teks panjang di-ellipsis: `white-space: nowrap; overflow: hidden; text-overflow: ellipsis;`.
- Baris aksi: dropdown `btn p-0 dropdown-toggle hide-arrow` → `bx bx-dots-vertical-rounded` → `dropdown-menu`.
- Baris expandable (Kontrak): `tr.clickable-row` + `tr.collapse.detail-row` + `.toggle-arrow` (lihat `index.spk.css`).

### Form
- `form-label` + `form-control` / `form-select` + `input-group`.
- **Error konsisten:** `class="form-control @error('field') is-invalid @enderror"` +
  `<div class="invalid-feedback">{{ $message }}</div>`.
- **Help text khas:** `<div class="form-text text-primary">[petunjuk]</div>` (pakai tanda kurung siku `[...]`).
- Spacing field: `mb-6` (dari template Sneat).
- Toggle: `form-check form-switch`.
- Tile pilihan multi (tim kerja): grid `.users-team-grid` + `.users-team-option`
  (`users/partials/tim-kerja-options.blade.php`).

### Tipografi
Font **Public Sans**. Heading halaman `<h4>` + deskripsi `<p class="text-muted">`. Judul card `<h5 class="mb-0">`.
Teks bantu `text-muted` / `.small`.

---

## 4. Pola Halaman per Modul

| Halaman | View | Pola |
|---|---|---|
| Dashboard | `dashboard/index.blade.php` | Header sapaan + konteks; grid 4 kartu statistik (`col-sm-6 col-xl-3`); grafik (`col-xl-8` + `col-xl-4`); daftar tindak lanjut & sebaran; tabel kegiatan terbaru. Ada skeleton shimmer, empty state, `<noscript>`. Data via `data-dashboard-charts` + `data-endpoint`. |
| Login | `login/index.blade.php` | Layout mandiri `page-auth.css` (bukan `<x-layout>`). |
| Kegiatan (index) | `kegiatan/index.blade.php` | 1 card; header: judul + search `input-group` + tombol Tambah (modal `#addKegiatanModal` → `@include('kegiatan.create')`); tabel + pagination. |
| Kegiatan (edit) | `kegiatan/edit.blade.php` → `partials/{form-edit,petugas-kegiatan}.blade.php` | Form edit (read-only bila `!$can_manage`); tombol **Aksi** (Generate/Unduh) via `btn-group`+dropdown; tabel petugas + modal tambah. |
| Kontrak/SPK | `kontrak/index.blade.php` | Filter bulan/tahun (`onchange="this.form.submit()"`); tabel expandable; checkbox massal + tombol Unduh. |
| Mitra | `mitra/index.blade.php` | Filter tahun + tombol Export Excel; tabel matriks 12 bulan (badge angka / `-`); pagination. |
| Sensus Ekonomi | `sensus-ekonomi/index.blade.php` + `partials/*` | Hero pencarian; filter bertingkat via **offcanvas drawer** (kanan) + chip filter aktif; daftar record (bukan tabel); pager AJAX kustom. |
| Petugas (edit) | `petugas/edit.blade.php` | Form tunggal dalam card. |
| Profil | `profil/index.blade.php` | Grid `col-xl-8` (data profil) + `col-xl-4` (reset password). |
| User | `users/{index,edit}.blade.php` + `partials/*` | Kartu statistik + toolbar filter (search/role/tim kerja) + tabel avatar; create via modal; edit 2 kolom. |
| Error | `errors/{403,404,503}.blade.php` | Layout mandiri `page-misc.css`; angka besar + ilustrasi + `btn btn-primary`. |

---

## 5. Komponen & Partials

Tidak ada `app/View/Components` (folder tidak ada). Semua "komponen" adalah file Blade murni:

| Komponen | Path | Fungsi |
|---|---|---|
| `<x-layout>` | `components/layout.blade.php` | Layout utama (slot) |
| `<x-sidebar />` | `components/sidebar.blade.php` | Navigasi samping |
| `<x-navbar />` | `components/navbar.blade.php` | (dinonaktifkan) |
| `<x-footer />` | `components/footer.blade.php` | Footer |
| `<x-alerts />` | `components/alerts.blade.php` | Flash message SweetAlert2 |

**Partials berulang (`@include` dengan parameter array):**
- `users/partials/{form-edit,tim-kerja-options}.blade.php`, `users/create.blade.php` (di dalam modal).
- `kegiatan/partials/{form-edit,petugas-kegiatan,unduh-ob,placeholder}.blade.php`, `kegiatan/create.blade.php`.
- `kontrak/partials/detail-kegiatan-petugas.blade.php`.
- `sensus-ekonomi/partials/{import-form,results}.blade.php`.

---

## 6. Ikon, Chart & Notifikasi

### Ikon — Boxicons
Umum: `bx bx-search`, `bx bx-plus`, `bx bx-edit-alt`, `bx bx-trash`, `bx bx-dots-vertical-rounded`,
`bx bx-chevron-down/left/right`, `bx bx-check-circle` / `bx bx-x-circle`, `bx bx-task`, `bx bx-file`,
`bx bx-group`, `bx bx-user-circle`, `bx bx-upload`, `bx bx-filter-alt`, `bxs-dashboard`, `bxs-shield-alt-2`.
Sering disertai `aria-hidden="true"`. Ukuran: `fs-5`, `bx-sm`, `bx-md`, `bx-xs`.

### Chart — ApexCharts
- Dimuat di `layout.blade.php`.
- Pola deklaratif: elemen pembawa `data-dashboard-charts` + `data-endpoint="{ route('dashboard.data') }"`,
  dirender oleh `public/js/dashboard.js`.
- Selalu sediakan skeleton (`dash-chart-skeleton`) + fallback error (`#dash-chart-error`) dan `<noscript>`.

### Notifikasi — SweetAlert2 (`components/alerts.blade.php`)
| Session key | Perilaku |
|---|---|
| `success` | Ikon success "Berhasil!" |
| `warning` | Confirm dialog → submit `#form-tambah-petugas` + input `bypass_ob_check` |
| `error` | Ikon error "Gagal!" (mendukung HTML) |
| `$errors->any()` | Semua error validasi digabung `<br>` |

Warna konsisten: `confirmButtonColor: '#696cff'`, `cancelButtonColor: '#d33'`.
Konfirmasi hapus/generate via file terpisah di `public/js/`: `confirm-delete.js`,
`confirm-delete-user.js`, `confirm-generate.js`.

---

## 7. Tabel, Filter, Pencarian, Pagination

### Tabel
Wrapper `table-responsive text-nowrap`; tabel `table table-hover` (+ `table-bordered` untuk matriks bulanan).
Nama dimaniskan dengan `ucwords(strtolower($nama))`.

### Filter
- **Auto-submit:** `<select class="form-select w-auto" onchange="this.form.submit()">` (tahun/bulan/role/tim kerja).
- Pencarian nama sisi klien: `#filter-nama` + `public/js/filter-nama.js`.
- Filter kompleks (Sensus): offcanvas `offcanvas-end` + segment button + chip filter aktif + "Bersihkan filter".

### Pencarian
- Server-side: `<form method="GET">` + `<input type="search" name="search">` di `input-group` + ikon `bg-label-primary`.
- Autocomplete mitra (live): input `#nama_mitra` + `<ul id="mitra-list">` + fetch `petugas.search`.

### Pagination
- **Bootstrap 5**: `Paginator::useBootstrapFive()` (`AppServiceProvider`).
- Markup: `<div class="pagination pagination-sm ...">{{ $x->onEachSide(0)->links() }}</div>`.
- Info jumlah: `Menampilkan {{ firstItem }}–{{ lastItem }} dari {{ total }} ...`.
- Pager AJAX kustom di Sensus (`data-sensus-page`).

---

## 8. Konvensi Penamaan & Bahasa

- **Bahasa label = Bahasa Indonesia** (mayoritas mutlak): "Tambah user", "Simpan perubahan", "Hapus",
  "Cari", "Honor", "Beban Kerja", "Tanggal Mulai/Selesai", "Wilayah Tugas", "Tim Kerja",
  "Belum ada data", "Menampilkan ... dari ... user".
- **Blade comment:** `{{-- ... --}}`.
- **Prefix kelas CSS per-halaman** untuk isolasi: `.dash-*`, `.users-*`, `.sensus-*`.
- **CSS variable per halaman**: pola `--<page>-<token>`.
- **JS per-halaman** di `public/js/`, didaftarkan via `<script src="{{ asset('js/...') }}">` **setelah** `</x-layout>`.
- **Fitur sementara** ditandai komentar `{{-- SEMENTARA ... --}}` + badge "sementara".
- **Data endpoint deklaratif**: atribut `data-*` + `route()`.
- **Helper global**: `formatNominal($value, $withRp = true)` → `"Rp 1.234.567"`; `parseNominal()`.
- **Tanggal**: `\Carbon\Carbon::parse(...)->translatedFormat('d M Y')` (locale `id`).

---

## 9. Checklist Halaman Baru

1. Bungkus dengan `<x-layout>`, isi `<div class="container-xxl flex-grow-1 container-p-y [prefix]-page">`.
2. Header: `<h4>` judul + `<p class="text-muted">` deskripsi + tombol aksi (`btn btn-primary`).
3. Gunakan `card` (header + body), tabel `table table-hover` dalam `table-responsive`, pagination Bootstrap 5.
4. Form: `form-label` + kontrol + `@error ... is-invalid` + `invalid-feedback` + help text `form-text text-primary` `[...]`.
5. Badge pakai `bg-label-*`; ikon pakai Boxicons; warna mengikuti `#696cff` / token `--<page>-*`.
6. CSS kustom baru → file di `public/css/` dengan prefix kelas `.halaman-*` dan token `--halaman-*`.
7. JS halaman baru → file di `public/js/`, daftarkan setelah `</x-layout>`.
8. Label berbahasa Indonesia; tandai fitur eksperimental dengan `{{-- SEMENTARA --}}`.

---

## 10. Utang Teknis UI

| Temuan | Dampak |
|---|---|
| Tailwind + Vite terpasang tetapi tidak dipakai (tidak ada `@vite` di Blade) | Kontributor bisa salah mengira Tailwind aktif |
| `resources/css/app.css` mendeklarasikan font `Instrument Sans` (sisa starter kit) | Tidak relevan — UI memakai Public Sans |
| `components/navbar.blade.php` masih placeholder ("John Doe", "Search...") | Dinonaktifkan, jangan dikira aktif |
| Sebagian teks template Sneat belum dilokalkan (error page, footer, tombol navbar) | Ada campuran Inggris/Indonesia |

---

## 11. Referensi

- `resources/views/components/*.blade.php`
- `resources/views/**/*.blade.php`
- `public/css/*.css`, `public/js/*.js`
- `app/Providers/AppServiceProvider.php` (paginator & locale)
- `app/Helpers/parseNominal.php`
- `vite.config.js`, `resources/css/app.css`
- Terkait: [`prd.md`](./prd.md), [`architecture.md`](./architecture.md)
