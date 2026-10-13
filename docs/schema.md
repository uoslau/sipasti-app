# Schema — SIPASTI

> Skema database SIPASTI. Disusun dari file migrasi di `database/migrations/` dan model di `app/Models/`.
> Tidak ada file `.sql`/dump — seluruh skema berasal dari migrasi.

---

## 1. Informasi Umum

| Aspek | Nilai |
|---|---|
| DBMS | MySQL |
| Nama database | `sipasti_app` (`.env` → `DB_DATABASE`) |
| Jumlah file migrasi | 12 file (membentuk 17 tabel, termasuk tabel bawaan Laravel) |
| SoftDeletes | `kegiatans`, `petugas_kegiatans`, `nomor_kontraks` |

### Daftar File Migrasi → Tabel
| File Migrasi | Tabel |
|---|---|
| `0001_01_01_000000_create_users_table.php` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` |
| `2025_08_03_040646_create_wilayah_tugas_table.php` | `wilayah_tugas` |
| `2025_08_03_081830_create_fungsis_table.php` | `fungsis` |
| `2025_08_03_094934_create_mitras_table.php` | `mitras` |
| `2025_08_03_094934_create_tim_kerjas_table.php` | `tim_kerjas` |
| `2025_08_03_094935_create_kegiatans_table.php` | `kegiatans` |
| `2025_08_05_154242_create_petugas_kegiatans_table.php` | `petugas_kegiatans` |
| `2025_08_23_120445_create_nomor_kontraks_table.php` | `nomor_kontraks` |
| `2025_09_05_024104_create_tim_kerja_user_table.php` | `tim_kerja_user` |
| `2026_09_22_000000_create_sensus_ekonomi_rows_table.php` | `sensus_ekonomi_rows` *(SEMENTARA)* |

---

## 2. Tabel Bawaan Laravel

### `users`
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| name | varchar | no | - | Nama user |
| username | varchar | no | - | **UNIQUE** (dipakai login) |
| email | varchar | ya | - | **UNIQUE** |
| email_verified_at | timestamp | ya | - | |
| password | varchar | no | - | |
| is_admin | boolean | no | `false` | Penanda admin |
| remember_token | varchar | ya | - | |
| created_at / updated_at | timestamp | ya | - | |

### `password_reset_tokens`
`email` (varchar, PK), `token` (varchar), `created_at` (timestamp, nullable).

### `sessions`
`id` (varchar, PK), `user_id` (bigint, index), `ip_address` (varchar 45), `user_agent` (text), `payload` (longText), `last_activity` (integer, index).

### `cache` / `cache_locks`
- `cache`: `key` (varchar PK), `value` (mediumText), `expiration` (integer).
- `cache_locks`: `key` (varchar PK), `owner` (varchar), `expiration` (integer).

### `jobs` / `job_batches` / `failed_jobs`
- `jobs`: `id` PK, `queue` (varchar, index), `payload` (longText), `attempts` (tinyint), `reserved_at`/`available_at`/`created_at` (int unsigned).
- `job_batches`: `id` (varchar PK), `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids` (longText), `options` (mediumText), `cancelled_at`, `created_at`, `finished_at`.
- `failed_jobs`: `id` PK, `uuid` (varchar UNIQUE), `connection` (text), `queue` (text), `payload` (longText), `exception` (longText), `failed_at` (timestamp).

> Laravel juga otomatis membuat tabel `migrations`.

---

## 3. Tabel Bisnis

### `fungsis`
*Migrasi: `2025_08_03_081830_create_fungsis_table.php`*
| Kolom | Tipe | Null | Default |
|---|---|---|---|
| id | bigint unsigned | no | auto |
| fungsi | varchar | no | - |
| created_at / updated_at | timestamp | ya | - |

### `tim_kerjas`
*Migrasi: `2025_08_03_094934_create_tim_kerjas_table.php`*
| Kolom | Tipe | Null | Default |
|---|---|---|---|
| id | bigint unsigned | no | auto |
| nama_tim_kerja | varchar | no | - |
| alias_tim_kerja | varchar | no | - |
| created_at / updated_at | timestamp | ya | - |

### `wilayah_tugas`
*Migrasi: `2025_08_03_040646_create_wilayah_tugas_table.php`*
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| kode_wilayah | varchar | no | - | **UNIQUE** (`1201`, `1225`) |
| nama_wilayah | varchar | no | - | Nias / Nias Barat |
| honor_pendataan | integer | no | - | Tarif honor pendataan |
| honor_pengolahan | integer | no | - | Tarif honor pengolahan |
| created_at / updated_at | timestamp | ya | - | |

### `mitras`
*Migrasi: `2025_08_03_094934_create_mitras_table.php`*
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| nik | varchar | no | - | **UNIQUE** (kunci relasi logis) |
| nama_mitra | varchar | no | - | |
| posisi | varchar | no | - | `Mitra Pendataan` / `Mitra Pengolahan` / `Mitra (Pendataan dan Pengolahan)` |
| email | varchar | no | - | |
| wilayah_id | bigint unsigned | no | - | FK → `wilayah_tugas.id` **ON DELETE CASCADE** |
| alamat | varchar | no | - | |
| tanggal_lahir | date | no | - | |
| npwp | varchar | ya | - | |
| jenis_kelamin | boolean | no | - | |
| pekerjaan | varchar | no | - | |
| created_at / updated_at | timestamp | ya | - | |

### `kegiatans`
*Migrasi: `2025_08_03_094935_create_kegiatans_table.php`*
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| nama_kegiatan | varchar | no | - | Sumber slug |
| is_ob | boolean | ya | `false` | Penanda kegiatan O-B |
| slug | varchar | no | - | **UNIQUE** (route key) |
| tanggal_mulai | date | no | - | |
| tanggal_selesai | date | no | - | Harus sebulan dengan `tanggal_mulai` (aturan aplikasi) |
| beban_anggaran | varchar | no | - | Mata anggaran / sumber dana |
| fungsi_id | bigint unsigned | no | `6` | FK → `fungsis.id` **ON DELETE CASCADE** |
| tim_kerja_id | bigint unsigned | no | `12` | FK → `tim_kerjas.id` **ON DELETE CASCADE** |
| honor_nias | integer | ya | - | Tarif honor Nias |
| honor_nias_barat | integer | ya | - | Tarif honor Nias Barat (warisan, wilayah dinonaktifkan) |
| is_generated | boolean | no | `false` | Sudah digenerate nomor SPK/BAST |
| deleted_at | timestamp | ya | - | **SoftDeletes** |
| created_at / updated_at | timestamp | ya | - | |

### `petugas_kegiatans`
*Migrasi: `2025_08_05_154242_create_petugas_kegiatans_table.php`*
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| nik | varchar | no | - | Relasi logis → `mitras.nik` (tanpa FK) |
| kegiatan_id | bigint unsigned | no | - | FK → `kegiatans.id` **ON DELETE CASCADE** |
| bertugas_sebagai | varchar | no | - | Mis. `PCL` |
| beban_kerja | integer | no | `0` | |
| satuan_beban_kerja | varchar | no | - | Mis. `Dokumen` |
| honor | integer | no | - | Hasil hitung honor |
| deleted_at | timestamp | ya | - | **SoftDeletes** |
| created_at / updated_at | timestamp | ya | - | |

### `nomor_kontraks`
*Migrasi: `2025_08_23_120445_create_nomor_kontraks_table.php`*
| Kolom | Tipe | Null | Default | Keterangan |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| nik | varchar | no | - | Relasi logis → `mitras.nik` / `petugas_kegiatans.nik` |
| kegiatan_id | bigint unsigned | no | - | FK → `kegiatans.id` **ON DELETE CASCADE** |
| tahun | integer | no | - | |
| bulan | integer | no | - | |
| nomor_kontrak | integer | no | - | Unik per tahun, dipakai ulang per petugas/bulan |
| nomor_bast | integer | no | - | Selalu increment |
| deleted_at | timestamp | ya | - | **SoftDeletes** |
| created_at / updated_at | timestamp | ya | - | |

### `tim_kerja_user` (pivot)
*Migrasi: `2025_09_05_024104_create_tim_kerja_user_table.php`*
| Kolom | Tipe | Keterangan |
|---|---|---|
| user_id | bigint unsigned | FK → `users.id` **ON DELETE CASCADE** |
| tim_kerja_id | bigint unsigned | FK → `tim_kerjas.id` **ON DELETE CASCADE** |

**Primary key gabungan:** `(user_id, tim_kerja_id)` — mencegah duplikasi user dalam satu tim.

### `sensus_ekonomi_rows` *(SEMENTARA)*
*Migrasi: `2026_09_22_000000_create_sensus_ekonomi_rows_table.php`*
| Kolom | Tipe | Null |
|---|---|---|
| id | bigint unsigned | no |
| email_pencacah | varchar | ya |
| level_2_full_code | varchar | ya |
| nama_kecamatan | varchar | ya |
| level_4_name | varchar | ya |
| level_5_name | varchar | ya |
| nama_kk | varchar | ya |
| nama_dtsen | varchar | ya |
| nik_dtsen | varchar(32) | ya |
| ada_keluarga_label | varchar | ya |
| catatan | text | ya |
| link_fasih | text | ya |
| created_at / updated_at | timestamp | ya |

> Modul berdiri sendiri, tidak berelasi dengan tabel SIPASTI lain. Aman dihapus.

---

## 4. Model

| Model | Tabel | Guarded/Fillable | Casts | Relasi | Catatan |
|---|---|---|---|---|---|
| `User` | `users` | `$fillable`: name, username, email, password, is_admin | password→hashed, email_verified_at→datetime, is_admin→boolean | `timKerja()` belongsToMany `TimKerja` | Helper `isAdmin()`, `timKerjaIds()` |
| `TimKerja` | `tim_kerjas` | default | - | `user()` belongsToMany `User` | |
| `Fungsi` | `fungsis` | default | - | - | Model kosong |
| `WilayahTugas` | `wilayah_tugas` | default | - | `mitra()` hasMany `Mitra` (wilayah_id) | Konstanta `KODE_NIAS = '1201'` |
| `Mitra` | `mitras` | default | - | `petugasKegiatan()` hasMany (nik↔nik); `wilayahTugas()` belongsTo | |
| `Kegiatan` | `kegiatans` | `$guarded = ['id','slug']` | is_generated→boolean | `fungsi()`, `timKerja()` belongsTo; `petugasKegiatan()` hasMany | HasSlug (dari `nama_kegiatan`), SoftDeletes, route key = `slug` |
| `PetugasKegiatan` | `petugas_kegiatans` | `$guarded = ['id']` | - | `kegiatan()`, `mitra()` belongsTo; `nomorKontrak()` hasOne; `timKerja()` hasOneThrough | SoftDeletes, route key = `nik` |
| `NomorKontrak` | `nomor_kontraks` | `$guarded = ['id']` | - | `petugasKegiatan()` belongsTo (nik↔nik) | SoftDeletes |
| `SensusEkonomiRow` | `sensus_ekonomi_rows` | `$guarded = ['id']` | - | - | Mandiri (SEMENTARA) |

### Perilaku cascade (model event `deleting`)
- Hapus `Kegiatan` → menghapus semua `PetugasKegiatan` terkait.
- Hapus `PetugasKegiatan` → menghapus `NomorKontrak` terkait.

### Relasi `timKerja` pada PetugasKegiatan
`hasOneThrough(TimKerja, Kegiatan, ...)` — Petugas → Kegiatan (`kegiatan_id`) → TimKerja (`tim_kerja_id`).

---

## 5. ERD Textual

```
users ──< tim_kerja_user >── tim_kerjas
  │                                ▲
  │ (auth)                         │ tim_kerja_id
  │                                │
  │                            kegiatans ──< petugas_kegiatans >── mitras
  │                                │              │                   │
  │                          fungsi_id       kegiatan_id         wilayah_id
  │                                ▼              │                   ▼
  │                            fungsis           │             wilayah_tugas
  │                                               ▼
  │                                         nomor_kontraks
  │                                               ▲
  └────────── (relasi logis via NIK) ─────────────┘

sensus_ekonomi_rows  (berdiri sendiri)
```

**Penjelasan hubungan:**
1. **User ↔ Tim Kerja** — M:N via `tim_kerja_user` (PK gabungan).
2. **Kegiatan → Fungsi** — N:1 (`kegiatans.fungsi_id` → `fungsis.id`, default `6`).
3. **Kegiatan → Tim Kerja** — N:1 (`kegiatans.tim_kerja_id` → `tim_kerjas.id`, default `12`).
4. **Kegiatan → Petugas Kegiatan** — 1:N (`petugas_kegiatans.kegiatan_id`).
5. **Mitra → Wilayah Tugas** — N:1 (`mitras.wilayah_id` → `wilayah_tugas.id`).
6. **Mitra ↔ Petugas Kegiatan** — relasi **via NIK** (bukan FK formal).
7. **Petugas Kegiatan ↔ Nomor Kontrak** — via **NIK** + `kegiatan_id`.
8. **Wilayah Tugas → Mitra** — 1:N.
9. **Sensus Ekonomi** — tabel terisolasi.

> ⚠️ **Integritas data:** relasi `mitras` ⇄ `petugas_kegiatans` ⇄ `nomor_kontraks` memakai **NIK tanpa
> foreign key constraint**. Konsistensi bergantung pada validasi aplikasi.

---

## 6. Seeder (`database/seeders`)

`DatabaseSeeder` memanggil berurutan: `TimKerjaSeeder`, `FungsiSeeder`, `WilayahTugasSeeder`,
`MitraSeeder`, `UserSeeder`, `KegiatanSeeder`, `PetugasKegiatanSeeder`.

| Seeder | Data |
|---|---|
| `TimKerjaSeeder` | 12 baris: PEMEJA, IPEK, SUPER, SP2K, KESRA, SKK, HANSOS, DISHAR, KTIP, NEPPRO, APSTAT, + baris `"null"` |
| `FungsiSeeder` | 6 baris: Distribusi, IPDS, NWAS, Produksi, Sosial, + `"null"` |
| `WilayahTugasSeeder` | `1201` Nias (pendataan 3.627.000 / pengolahan 3.355.000); `1225` Nias Barat (3.704.000 / 3.426.000) |
| `MitraSeeder` | `Mitra::factory()->count(250)` |
| `UserSeeder` | 1 admin (`admin`/`admin`) + 24 user `@bps.go.id` (password = username); 9 user di-attach ke tim kerja |
| `KegiatanSeeder` | `Kegiatan::factory()->count(50)` |
| `PetugasKegiatanSeeder` | `PetugasKegiatan::factory()->count(500)` |

**Akun default:** `admin` / `admin` (is_admin = 1).

**Pemetaan user → tim kerja** (`UserSeeder`): yuris.tz→1, gregorius.gulo→2, faberlius.hulu→3,
geni.harefa→5, anugrah.marbun→6, alfrince.hulu→7, hiskia.harefa→8, kurniaman.harefa→9, trisno.harefa→10.

---

## 7. Factory (`database/factories`)

| Factory | Model | Detail |
|---|---|---|
| `UserFactory` | User | name & email unik, password `password`, `unverified()` state |
| `TimKerjaFactory` | TimKerja | nama & alias acak |
| `MitraFactory` | Mitra | nik unik, posisi acak (3 opsi), wilayah_id 1–2 |
| `KegiatanFactory` | Kegiatan | tanggal mulai/selesai, tim_kerja_id 1–11, fungsi_id=6, honor_nias & honor_nias_barat acak |
| `PetugasKegiatanFactory` | PetugasKegiatan | nik acak dari `Mitra`, kegiatan_id 1–50, bertugas_sebagai `PCL`, beban_kerja 1–10 |

> Catatan: `WilayahTugasSeeder`/`WilayahTugasFactory` direferensikan di model, namun seeder wilayah
> di atas dibuat langsung via `WilayahTugas::create()`.

---

## 8. Referensi

- `database/migrations/*.php`
- `database/seeders/*.php`
- `database/factories/*.php`
- `app/Models/*.php`
- Terkait: [`architecture.md`](./architecture.md)
