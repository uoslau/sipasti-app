# Architecture — SIPASTI

> Arsitektur teknis aplikasi SIPASTI. Semua temuan diverifikasi langsung dari kode sumber.

---

## 1. Stack & Dependensi

### Backend
| Komponen | Versi / Keterangan |
|---|---|
| PHP | `^8.2` |
| Framework | `laravel/framework: ^12.0` |
| Database | MySQL (`.env` → `DB_DATABASE=sipasti_app`) |
| Tinker | `laravel/tinker: ^2.10.1` |

**Paket fungsional penting:**
| Paket | Fungsi |
|---|---|
| `maatwebsite/excel: ^3.1` | Import & export Excel (PhpSpreadsheet) |
| `phpoffice/phpword: ^1.4` | Generate dokumen Word (SPK/BAST/OB) via `TemplateProcessor` |
| `spatie/laravel-sluggable: ^3.7` | Slug otomatis untuk `Kegiatan` |

**Dev:** `pestphp/pest`, `laravel/pint`, `laravel/pail`, `kitloong/laravel-migrations-generator`, `fakerphp/faker`.

### Frontend
| Komponen | Keterangan |
|---|---|
| View | **Blade** (server-side) |
| CSS | **Bootstrap 5** (template Sneat) + CSS kustom di `public/css/` |
| JS | jQuery, Bootstrap bundle, Popper, perfect-scrollbar |
| Ikon | Boxicons |
| Chart | ApexCharts |
| Notifikasi | SweetAlert2 (CDN) |
| Build tool | Vite 7 (+ `tailwindcss` terpasang, **tidak dipakai**) |

> Lihat [`ui-guidelines.md`](./ui-guidelines.md) untuk detail frontend.

---

## 2. Struktur Folder

```
app/
├── Exceptions/Handler.php              # render 404 kustom
├── Exports/RekapMitraExport.php        # export Excel rekap mitra
├── Helpers/
│   ├── parseNominal.php                # parseNominal() & formatNominal() (autoload global)
│   └── NumberToWords.php               # toWords(), monthName(), dayName()
├── Http/
│   ├── Controllers/                    # 11 controller + Traits/GeneratesBastDocuments.php
│   ├── Middleware/                     # EnsureUserIsAdmin, PreventBackHistory, EnsureSensusEkonomiAccess
│   └── Requests/                       # StoreMitraRequest, UpdateMitraRequest (stub)
├── Imports/                            # PetugasImport, PetugasImportUpdate, SensusEkonomiImport
├── Models/                             # 9 model
├── Policies/                           # KegiatanPolicy (+ Mitra/PetugasKegiatan stub)
└── Providers/AppServiceProvider.php
bootstrap/
├── app.php                             # konfigurasi routing, middleware alias, exceptions (Laravel 11+)
└── providers.php                       # hanya AppServiceProvider
routes/
├── web.php                             # SEMUA route (tidak ada api.php / auth.php)
└── console.php                         # closure command `inspire`
resources/views/                        # Blade per modul + components/
public/{css,js,assets}/                 # CSS/JS kustom + aset template Sneat
storage/app/public/template/            # template_bast.docx, template_kontrak.docx
storage/app/public/template_ob/         # template OB per kegiatan (upload)
```

---

## 3. Pola Arsitektur

### 3.1 MVC "Fat Controller"
Mayoritas logika bisnis berada di **controller** — tidak ada Service Layer, Repository, atau Action class.
Query berat memakai **Query Builder + `DB::raw`** (mis. perhitungan ulang honor, rekap kontrak), bukan
hanya Eloquent.

### 3.2 Trait sebagai "mesin" bersama
`app/Http/Controllers/Traits/GeneratesBastDocuments.php` adalah satu-satunya pemisahan logika yang
menyerupai service layer. Trait ini menyediakan `generateAndZipBastDocuments()` dan dipakai bersama oleh
**`KegiatanController`** dan **`DownloadController`** untuk menghasilkan dokumen BAST/OB dan mengemasnya
ke ZIP.

### 3.3 Helper global
`app/Helpers/parseNominal.php` di-autoload via `composer.json` (`autoload.files`), sehingga
`parseNominal()` dan `formatNominal()` tersedia di seluruh aplikasi (view & controller):

- `parseNominal($value)` → hilangkan pemisah ribuan, kembalikan `int`.
- `formatNominal($value, $withRp = true)` → `"Rp 1.234.567"`.

`NumberToWords` menyediakan konversi angka ke kata (terbilang), nama bulan, dan nama hari (Bahasa Indonesia)
untuk keperluan dokumen kontrak.

### 3.4 Model events (cascade soft delete)
Cascade dilakukan di level aplikasi (bukan FK DB), pada method `booted()`:

- `Kegiatan::deleting` → menghapus semua `PetugasKegiatan` terkait.
- `PetugasKegiatan::deleting` → menghapus `NomorKontrak` terkait.

### 3.5 SoftDeletes
Diterapkan pada `kegiatans`, `petugas_kegiatans`, dan `nomor_kontraks`.

---

## 4. Autentikasi

Autentikasi **kustom** (bukan Breeze/Jetstream/Fortify):

- **Login memakai `username`** + password (`Auth::attempt`).
- Guard `web` (session) + provider Eloquent `App\Models\User` (`config/auth.php`).
- **Rate limiting ganda** (`RateLimiter`) di `LoginController`:
  - Per akun + IP: **5** percobaan, decay **60 detik**.
  - Per IP: **20** percobaan, decay **300 detik** (menahan percobaan ganti-ganti username).
- Sukses → `session()->regenerate()` lalu redirect `intended` ke dashboard.
- Logout → `Auth::logout()` + `invalidate()` + `regenerateToken()`.
- Tidak ada verifikasi email wajib (`MustVerifyEmail` dikomentari); middleware `verified` tidak dipakai.

---

## 5. Otorisasi

### 5.1 Gate::before (admin bypass)
Di `AppServiceProvider::boot()`:

```php
Gate::before(function ($user, $ability) {
    return $user && $user->is_admin ? true : null;
});
```

Admin otomatis lolos **semua** ability/policy. Non-admin dievaluasi normal.

### 5.2 Policy
`KegiatanPolicy` (aktif) — `viewAny`/`view` = `true` untuk semua; `create` = punya tim kerja;
`update`/`delete` = `owns()` (tim kerja kegiatan ada di `timKerjaIds()` user).

`MitraPolicy` & `PetugasKegiatanPolicy` masih **stub** (semua `false`) — otorisasi petugas/mitra
dilakukan manual di controller lewat `$this->authorize('update', $kegiatan)`.

### 5.3 Middleware
| Alias | Kelas | Fungsi |
|---|---|---|
| `admin` | `EnsureUserIsAdmin` | `abort(403)` bila bukan admin |
| `prevent-back-history` | `PreventBackHistory` | Header no-cache agar tombol back tidak membuka halaman ter-autentikasi (dilewati untuk `BinaryFileResponse`/unduhan) |
| *(langsung)* | `EnsureSensusEkonomiAccess` | Akses modul Sensus Ekonomi = admin ATAU tim `PEMEJA`. Punya `allows()` statis yang dipakai bersama sidebar |

### 5.4 Basis scoping data
- `User::timKerjaIds()` mengembalikan id tim kerja user.
- Dipakai untuk scoping di `DashboardController`, `KegiatanPolicy`, `KegiatanController`, dll.
- Tim kerja id `1` diperlakukan istimewa di rekap (menentukan honor pengolahan).

---

## 6. Alur Bisnis Utama

```
1. Buat Kegiatan
   User (anggota tim kerja) / admin menambah kegiatan.
   Validasi: tanggal mulai & selesai sebulan yang sama, nama tanpa "/" & "\".
   honor_nias disimpan sebagai tarif dasar.

2. Tambah Petugas (Mitra)
   Pilih mitra wilayah Nias  → set bertugas_sebagai, beban_kerja, satuan.
   honor dihitung = honor_nias × beban_kerja (flat honor_nias bila kegiatan O-B).
   Dicek: duplikasi NIK dalam kegiatan & bentrok kegiatan O-B sebulan.

3. Generate Nomor
   NomorKontrakController::generate() dalam 1 transaksi:
     - nomor_kontrak unik per tahun (dipakai ulang bila petugas sudah punya kontrak di bulan yang sama)
     - nomor_bast selalu increment
     - kegiatan.is_generated = true

4. Rekap & Unduh
   KontrakController  → rekap honor per petugas/bulan + honor_max + deteksi kelebihan.
   DownloadController → generate Word (TemplateProcessor + ZipArchive):
     - downloadBAST : BAST per mitra dari template_bast.docx
     - downloadSPK  : SPK per mitra dari template_kontrak.docx (petugas O-B dikecualikan)
     - uploadOB/downloadOB : dokumen OB per kegiatan
```

---

## 7. Import / Export & Generate Word

### Excel (Maatwebsite)
| Kelas | Arah | Catatan |
|---|---|---|
| `RekapMitraExport` | Export | `FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles` |
| `PetugasImport` | Import | `ToCollection`; validasi per baris, transaksi rollback bila ada error |
| `PetugasImportUpdate` | Import | `ToCollection`; update data petugas yang sudah ada |
| `SensusEkonomiImport` | Import | `WithChunkReading` (1000 baris) + bulk insert, alias kolom, validasi heading |

### Word (PHPWord + ZipArchive)
- Template default: `storage/app/public/template/template_bast.docx` & `template_kontrak.docx`.
- Template OB: `storage/app/public/template_ob/{slug}.docx`.
- `TemplateProcessor::setValues()` + `cloneRow()` untuk baris kegiatan; hasil `.docx` dikemas dengan
  `ZipArchive` lalu dikirim via `response()->download()->deleteFileAfterSend(true)`.

---

## 8. Diagram Relasi (ringkas)

```
users ──< tim_kerja_user >── tim_kerjas
                                  ▲ tim_kerja_id
                                  │
                              kegiatans ──< petugas_kegiatans >── mitras
                                  │ fungsi_id          │  (relasi NIK)   │ wilayah_id
                                  ▼                    ▼                 ▼
                              fungsis            nomor_kontraks     wilayah_tugas

sensus_ekonomi_rows  (terisolasi — modul SEMENTARA)
```

Detail lengkap: [`schema.md`](./schema.md).

---

## 9. Konfigurasi Runtime

| Aspek | Nilai | Sumber |
|---|---|---|
| Timezone | `Asia/Jakarta` | `config/app.php` |
| Locale Carbon | `id` | `AppServiceProvider` |
| Pagination | Bootstrap 5 (`Paginator::useBootstrapFive()`) | `AppServiceProvider` |
| Session driver | `database` | `.env` |
| Queue | `database` | `.env` |
| Cache | `database` | `.env` |
| Filesystem | `local` (template & dokumen di disk `public`) | `.env` |
| Health check | `GET /up` | `bootstrap/app.php` |

---

## 10. Utang Teknis & Rekomendasi

| # | Temuan | Dampak | Rekomendasi |
|---|---|---|---|
| 1 | Logika bisnis padat di controller ("fat controller") | Sulit diuji & dipelihara | Ekstrak ke Service/Action bertahap, mulai dari perhitungan honor & generate nomor |
| 2 | `MitraPolicy` & `PetugasKegiatanPolicy` stub (`false`) | Otorisasi tersebar manual | Implementasikan policy & pakai konsisten |
| 3 | CRUD `MitraController` kosong; `Store/UpdateMitraRequest` `authorize()=false` | Fitur mitra tidak lengkap | Lengkapi atau nyatakan read-only secara eksplisit |
| 4 | Sisa logika honor Nias Barat di `update`/`KontrakController` meski pendaftaran dinonaktifkan | Potensi inkonsistensi | Bersihkan atau cadangkan nilainya |
| 5 | `APP_NAME=**Laravel**` | Branding tidak konsisten | Set `APP_NAME=SIPASTI` |
| 6 | Tailwind/Vite terpasang tapi tidak dipakai (tidak ada `@vite` di Blade) | Kebingungan kontributor | Hapus dependensi & konfigurasi yang tidak terpakai, atau pakai sungguhan |
| 7 | `resources/js/app.js` hanya berisi axios | Tidak ada bundling JS halaman | JS halaman saat ini via `public/js/` (dokumentasikan, lihat ui-guidelines) |
| 8 | Relasi via NIK tanpa FK (`mitras` ⇄ `petugas_kegiatans` ⇄ `nomor_kontraks`) | Integritas data bergantung aplikasi | Pertimbangkan FK/validasi ketat |

---

## 11. Referensi

- `composer.json`, `package.json`, `.env.example`
- `bootstrap/app.php`, `bootstrap/providers.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/*`, `app/Http/Controllers/Traits/GeneratesBastDocuments.php`
- `app/Http/Middleware/*`, `app/Policies/*`
- `app/Imports/*`, `app/Exports/RekapMitraExport.php`
- `app/Helpers/*`
