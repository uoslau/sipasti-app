# Routes — SIPASTI

> Daftar route aplikasi SIPASTI. Seluruh route HTTP didefinisikan di **`routes/web.php`**
> (Laravel 12 — tidak ada `routes/api.php` maupun `routes/auth.php`).

---

## 1. Konfigurasi Routing

**`bootstrap/app.php`:**
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        'admin'                => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

| Hal | Keterangan |
|---|---|
| `routes/api.php` | ❌ Tidak ada |
| `routes/auth.php` | ❌ Tidak ada (route auth ditulis langsung di `web.php`) |
| `RouteServiceProvider` | ❌ Tidak ada (dihapus di Laravel 11+) |
| `app/Http/Kernel.php` | ❌ Tidak ada (diganti `bootstrap/app.php`) |
| Health check | ✅ `GET /up` |

**Tidak ada** penggunaan `Route::resource()` / `Route::apiResource()` — semua route dideklarasikan manual.

---

## 2. Middleware

### Alias kustom (`bootstrap/app.php`)
| Alias | Kelas | Fungsi |
|---|---|---|
| `admin` | `EnsureUserIsAdmin` | `abort(403)` bila bukan admin |
| `prevent-back-history` | `PreventBackHistory` | Header no-cache (dilewati untuk unduhan) |

### Middleware non-alias
| Kelas | Dipakai via | Fungsi |
|---|---|---|
| `EnsureSensusEkonomiAccess` | FQCN langsung di `web.php` | Akses = admin ATAU tim `PEMEJA` |

### Group middleware framework
- `web` (default: session, CSRF, cookie) membungkus semua.
- `auth` + `prevent-back-history` membungkus seluruh aplikasi kecuali login/logout.
- `guest` hanya pada `GET /login`.

> Middleware `verified` **tidak dipakai** (tidak ada verifikasi email).

---

## 3. Route Model Binding

| Parameter | Model | Key | Lokasi |
|---|---|---|---|
| `{kegiatan}` | `Kegiatan` | **`slug`** | `app/Models/Kegiatan.php` (`getRouteKeyName()`) |
| `{petugasKegiatan}` | `PetugasKegiatan` | **`nik`** | `app/Models/PetugasKegiatan.php` (`getRouteKeyName()`) |
| `{user}` | `User` | `id` (default) | — |

---

## 4. Daftar Route per Modul

### 4.1 Autentikasi (`routes/web.php`)
| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/login` | `login` | `LoginController@index` | `guest` |
| POST | `/login` | — | `LoginController@authenticate` | — |
| POST | `/logout` | `logout` | `LoginController@logout` | — |

> Login via `username` + password dengan rate limiting (5/akun+IP, 20/IP).

### 4.2 Dashboard
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/` | `dashboard.index` | `DashboardController@index` |
| GET | `/dashboard/data` | `dashboard.data` | `DashboardController@getChartData` |

### 4.3 Kegiatan
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/kegiatan` | `kegiatan.index` | `KegiatanController@index` |
| POST | `/kegiatan` | `kegiatan.store` | `KegiatanController@store` |
| GET | `/kegiatan/{kegiatan}/edit-kegiatan` | `kegiatan.edit` | `KegiatanController@edit` |
| PUT | `/kegiatan/{kegiatan}` | `kegiatan.update` | `KegiatanController@update` |
| DELETE | `/kegiatan/{kegiatan}` | `kegiatan.destroy` | `KegiatanController@destroy` |

### 4.4 Petugas Kegiatan
| Method | URI | Name | Action |
|---|---|---|---|
| DELETE | `/kegiatan/{kegiatan}/{petugasKegiatan}` | `petugas.destroy` | `PetugasKegiatanController@destroy` |
| GET | `/search-mitra` | `petugas.search` | `PetugasKegiatanController@search` |
| POST | `/kegiatan/{kegiatan}/edit-kegiatan` | `petugas.store` | `PetugasKegiatanController@store` |
| GET | `/kegiatan/{kegiatan}/{petugasKegiatan}/edit-petugas` | `petugas.edit` | `PetugasKegiatanController@edit` |
| PUT | `/kegiatan/{kegiatan}/{petugasKegiatan}/edit-petugas` | `petugas.update` | `PetugasKegiatanController@update` |
| POST | `/kegiatan/{kegiatan}/petugas-import` | `petugas.import` | `PetugasKegiatanController@import` |
| POST | `/kegiatan/{kegiatan}/petugas-import-update` | `petugas.import_update` | `PetugasKegiatanController@import_update` |

### 4.5 Nomor Kontrak (Generate)
| Method | URI | Name | Action |
|---|---|---|---|
| POST | `/kegiatan/{kegiatan}/edit-kegiatan/generate` | `kontrak.generate` | `NomorKontrakController@generate` |

### 4.6 Download / Export Dokumen
> Semua memakai `->withoutMiddleware('prevent-back-history')`.

| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/kegiatan/download/{kegiatan}` | `kegiatan.download` | `DownloadController@downloadBAST` |
| GET | `/kontrak/{slug}` | `kontrak.download` | `DownloadController@downloadSPK` |
| POST | `/kegiatan/download/{kegiatan}/ob` | `kegiatanob.upload` | `DownloadController@uploadOB` |
| GET | `/kegiatan/download/{kegiatan}/ob` | `kegiatanob.download` | `DownloadController@downloadOB` |
| GET | `/download/{nama_file}` | `file.download` | Closure (unduh template dari `storage/app/public/template`) |

### 4.7 Kontrak (Rekap Bulanan)
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/kontrak` | `kontrak.index` | `KontrakController@index` |

### 4.8 Mitra
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/mitra` | `mitra.index` | `MitraController@index` |
| GET | `/mitra/rekap-kegiatan/export` | `mitra.export` | `MitraController@exportExcel` |

### 4.9 Sensus Ekonomi *(SEMENTARA)*
> Sub-grup: `middleware(EnsureSensusEkonomiAccess::class)`, `prefix('sensus-ekonomi')`, `name('sensus-ekonomi.')`.
> Akses: admin atau tim kerja `PEMEJA`.

| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/sensus-ekonomi/` | `sensus-ekonomi.index` | `SensusEkonomiController@index` |
| POST | `/sensus-ekonomi/import` | `sensus-ekonomi.import` | `SensusEkonomiController@import` |
| GET | `/sensus-ekonomi/hasil` | `sensus-ekonomi.results` | `SensusEkonomiController@results` |
| DELETE | `/sensus-ekonomi/` | `sensus-ekonomi.destroy` | `SensusEkonomiController@destroy` |

### 4.10 Users (khusus admin)
> Sub-grup: `middleware('admin')`, `prefix('users')`, `name('users.')`.

| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/users` | `users.index` | `UserController@index` |
| POST | `/users` | `users.store` | `UserController@store` |
| GET | `/users/{user}/edit` | `users.edit` | `UserController@edit` |
| PUT | `/users/{user}` | `users.update` | `UserController@update` |
| DELETE | `/users/{user}` | `users.destroy` | `UserController@destroy` |
| POST | `/users/{user}/reset-password` | `users.reset_password` | `UserController@resetPassword` |

### 4.11 Profil
> Sub-grup: `prefix('profil')`, `name('profil.')` (hanya butuh login).

| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/profil` | `profil.index` | `ProfileController@index` |
| PUT | `/profil` | `profil.update` | `ProfileController@update` |
| PUT | `/profil/password` | `profil.password` | `ProfileController@updatePassword` |

### 4.12 Lain-lain
| Method | URI | Keterangan |
|---|---|---|
| GET | `/up` | Health check (bawaan `withRouting(health: '/up')`) |
| — | `routes/console.php` | Closure command `inspire` (bukan route HTTP) |

---

## 5. Ringkasan Controller → Route Name

| Controller | Route name |
|---|---|
| `LoginController` | `login`, (POST login), `logout` |
| `DashboardController` | `dashboard.index`, `dashboard.data` |
| `KegiatanController` | `kegiatan.index`, `kegiatan.store`, `kegiatan.edit`, `kegiatan.update`, `kegiatan.destroy` |
| `PetugasKegiatanController` | `petugas.search`, `petugas.store`, `petugas.edit`, `petugas.update`, `petugas.destroy`, `petugas.import`, `petugas.import_update` |
| `NomorKontrakController` | `kontrak.generate` |
| `DownloadController` | `kegiatan.download`, `kontrak.download`, `kegiatanob.upload`, `kegiatanob.download` (+ closure `file.download`) |
| `KontrakController` | `kontrak.index` |
| `MitraController` | `mitra.index`, `mitra.export` |
| `SensusEkonomiController` | `sensus-ekonomi.index`, `sensus-ekonomi.import`, `sensus-ekonomi.results`, `sensus-ekonomi.destroy` |
| `UserController` | `users.index`, `users.store`, `users.edit`, `users.update`, `users.destroy`, `users.reset_password` |
| `ProfileController` | `profil.index`, `profil.update`, `profil.password` |

---

## 6. Catatan

- **Grup `auth` + `prevent-back-history`** membungkus hampir seluruh aplikasi.
- **Otorisasi per-aksi** dilakukan di controller lewat `$this->authorize(...)` terhadap `KegiatanPolicy`
  (bukan di route), dengan admin bypass via `Gate::before`.
- Method `create`/`show` di beberapa controller **kosong** dan tidak dirutekan.
- Modul Sensus Ekonomi ditandai `SEMENTARA` — blok route-nya dapat dihapus bersih.
- `routes/console.php` hanya mendefinisikan command contoh `inspire`.

---

## 7. Referensi

- `routes/web.php`, `routes/console.php`
- `bootstrap/app.php`, `bootstrap/providers.php`
- `app/Models/Kegiatan.php`, `app/Models/PetugasKegiatan.php`
- Terkait: [`architecture.md`](./architecture.md), [`prd.md`](./prd.md)
