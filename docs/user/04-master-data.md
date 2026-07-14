# Master Data

Modul Master Data merupakan fondasi sistem Braga8 Utility Billing. Data yang dikelola di modul ini menjadi acuan untuk seluruh operasi harian: pembacaan meter, penagihan, pembayaran, hingga pelaporan. Modul ini terdiri dari lima entitas utama yang saling berhubungan:

```text
Tariff ─┐
        ├─→ UtilityMeter ─→ Unit ─→ Tenant ─→ User
        ┘        (meter)    (unit)  (penyewa) (akun)
```

> **Akses:** Manajemen master data ditujukan untuk peran `admin` (penuh) dan `supervisor` (terbatas). Peran `petugas` dan `tenant` tidak memiliki akses tulis ke modul ini. Rute resource: `users`, `tenants`, `units`, `utility-meters`, `tariffs`.
>
> **Catatan Keamanan:** Beberapa rute resource tidak menerapkan middleware `role` secara konsisten. Lihat `SECURITY_AUDIT_REPORT.md` dan [02-roles-and-access.md](02-roles-and-access.md) untuk detail celah BOLA. Panduan ini mendokumentasikan perilaku yang dimaksud (intended behaviour).

## 1. Manajemen Pengguna (Users)

Modul Users mengelola akun login seluruh pengguna sistem, mencakup admin, supervisor, petugas, dan tenant.

> **Rute:** `Route::resource('users', UserController::class)` → `GET /users`, `GET /users/create`, `POST /users`, `GET /users/{user}/edit`, `PUT /users/{user}`, `DELETE /users/{user}`, `GET /users/{user}`.

### 1.1 Daftar Pengguna

Halaman `GET /users` menampilkan daftar pengguna dalam bentuk tabel paginasi (10 entri per halaman). Fitur pencarian tersedia melalui parameter `search` yang mencari pada kolom:

- `name` (nama lengkap)
- `username` (nama pengguna)
- `email` (surel)

Filter berdasarkan peran tersedia melalui parameter `role` dengan nilai: `admin`, `supervisor`, `petugas`, atau `tenant`.

### 1.2 Menambah Pengguna

Form tambah pengguna (`GET /users/create`) memerlukan isian berikut:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Nama (`name`) | Ya | `required`, max 255 | Nama lengkap pengguna |
| Username (`username`) | Ya | `required`, max 255, unik di `users` | Nama pengguna untuk login |
| Email (`email`) | Ya | `required`, `email`, max 255, unik di `users` | Surel pengguna |
| No. Telepon (`phone_number`) | Tidak | opsional, max 20 | Nomor kontak |
| Peran (`role`) | Ya | `required`, `in:admin,supervisor,petugas,tenant` | Hak akses pengguna |
| Password | Ya | `required`, min 6 karakter | Kata sandi login |

Setelah submit (`POST /users`), pengguna baru disimpan dan diarahkan kembali ke daftar pengguna dengan status `user-created`.

### 1.3 Mengubah Pengguna

Form ubah pengguna (`GET /users/{user}/edit`) memungkinkan memperbarui data pengguna. Aturan validasi sama dengan penambahan, kecuali:

- `username` dan `email` divalidasi unik dengan mengecualikan ID pengguna yang sedang diubah (`unique:users,username,{id}` dan `unique:users,email,{id}`).
- `password` bersifat opsional; bila dikosongkan, password lama dipertahankan.

Submit melalui `PUT /users/{user}` mengembalikan status `user-updated`.

### 1.4 Menghapus Pengguna

Tombol hapus (`DELETE /users/{user}`) menghapus akun pengguna. Konfirmasi diperlukan sebelum penghapusan. Status yang ditampilkan: `user-deleted`.

> ⚠️ **Perhatian:** Menghapus pengguna bertipe `tenant` sebaiknya dilakukan melalui modul Tenants agar relasi data penyewa ikut ditangani. Lihat [§2.4](#24-menghapus-penyewa).

### 1.5 Melihat Detail Pengguna

Halaman `GET /users/{user}` menampilkan informasi lengkap pengguna, termasuk profil dan (untuk tenant) data penyewa terkait.

### 1.6 Pembaruan Profil Mandiri

Selain perubahan oleh admin, setiap pengguna dapat memperbarui profilnya sendiri melalui metode `updateProfile`. Ini digunakan pada halaman profil pengguna (lihat [09-profile-notifications.md](09-profile-notifications.md)). Aturan validasi:

- `name`, `username`, `email` wajib diisi
- `phone_number` opsional
- `password` opsional (min 6 karakter bila diisi)
- Untuk tenant, field tambahan: `tenant_name`, `person_in_charge`, `business_type`, `contact_phone`, `contact_email`, `company_name`

## 2. Manajemen Penyewa (Tenants)

Modul Tenants mengelola data penyewa unit properti Braga8. Setiap tenant otomatis memiliki akun pengguna dengan peran `tenant` yang terhubung melalui `user_id`.

> **Rute:** `Route::resource('tenants', TenantController::class)` → `GET /tenants`, `GET /tenants/create`, `POST /tenants`, `GET /tenants/{tenant}`, `GET /tenants/{tenant}/edit`, `PUT /tenants/{tenant}`, `DELETE /tenants/{tenant}`.

### 2.1 Daftar Penyewa

Halaman `GET /tenants` menampilkan daftar penyewa dengan paginasi 10 entri per halaman. Fitur pencarian (`search`) mencari pada kolom:

- `tenant_name` (nama penyewa/tenant)
- `person_in_charge` (penanggung jawab)

Setiap entri memuat relasi `units.meters.readings` untuk menampilkan riwayat pembacaan meter terbaru. Bila request meminta JSON (`Accept: application/json`), data dikembalikan sebagai JSON (mendukung konsumsi API).

### 2.2 Menambah Penyewa

Form tambah penyewa (`GET /tenants/create`) memerlukan isian berikut:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Nama Penyewa (`tenant_name`) | Ya | `required`, max 255 | Nama tenant/usaha |
| Penanggung Jawab (`person_in_charge`) | Ya | `required`, max 255 | Nama PIC |
| Jenis Usaha (`business_type`) | Ya | `required`, max 255 | Kategori usaha |
| No. Telepon (`contact_phone`) | Ya | `required`, max 20 | Kontak telepon |
| Email (`contact_email`) | Ya | `required`, `email`, max 255, unik di `users` | Sekaligus email login |
| Nama Perusahaan (`company_name`) | Tidak | opsional, max 255 | Nama badan usaha (bila ada) |

Submit (`POST /tenants`) menjalankan transaksi database:

1. Membuat akun `User` dengan:
   - `name` = `person_in_charge`
   - `email` = `contact_email`
   - `username` = slug dari `person_in_charge` + 2 digit acak (mis. `john-doe-42`)
   - `password` = `password123` (default, wajib diganti setelah login pertama)
   - `role` = `tenant`
   - `phone_number` = `contact_phone`
2. Membuat record `Tenant` yang terhubung ke `user_id` baru.

Status yang ditampilkan: `tenant-created`.

> ⚠️ **Penting:** Password default `password123` harus segera diganti oleh tenant melalui halaman profil setelah login pertama untuk menjaga keamanan akun.

### 2.3 Mengubah Penyewa

Form ubah penyewa (`GET /tenants/{tenant}/edit`) memungkinkan memperbarui data tenant. Aturan validasi sama dengan penambahan, kecuali `contact_email` divalidasi unik dengan mengecualikan `user_id` tenant (`unique:users,email,{user_id}`).

Submit (`PUT /tenants/{tenant}`) menjalankan transaksi:

1. Memperbarui record `Tenant`.
2. Memperbarui record `User` terkait: `name`, `email`, dan `phone_number` disinkronkan dengan data tenant.

Status yang ditampilkan: `tenant-updated`.

### 2.4 Menghapus Penyewa

Tombol hapus (`DELETE /tenants/{tenant}`) menjalankan transaksi:

1. Menghapus akun `User` yang terhubung (`user_id`).
2. Menghapus record `Tenant`.

Status yang ditampilkan: `tenant-deleted`.

> ⚠️ **Efek Beruntun:** Menghapus penyewa juga menghapus akun login tenant. Pastikan tidak ada invoice/pembayaran aktif yang masih terkait sebelum menghapus.

### 2.5 Melihat Detail Penyewa

Halaman `GET /tenants/{tenant}` menampilkan informasi lengkap penyewa, termasuk unit-unit yang ditempati dan meter-meternya.

## 3. Manajemen Unit

Modul Units mengelola unit-unit properti yang disewakan kepada tenant. Satu tenant dapat memiliki beberapa unit.

> **Rute:** `Route::resource('units', UnitController::class)` → `GET /units`, `GET /units/create`, `POST /units`, `GET /units/{unit}`, `GET /units/{unit}/edit`, `PUT /units/{unit}`, `DELETE /units/{unit}`.

### 3.1 Daftar Unit

Halaman `GET /units` menampilkan daftar unit yang dikelompokkan per tenant. Paginasi 5 entri per halaman. Fitur pencarian (`search`) mencari pada:

- `tenant_name` (nama penyewa pemilik unit)
- `unit_number` (nomor unit, melalui relasi `orWhereHas('units', ...)`)

Daftar semua tenant (`$allTenants`) juga dimuat untuk keperluan filter/form tambah cepat di halaman yang sama.

### 3.2 Menambah Unit

Form tambah unit (`GET /units/create`) memerlukan isian:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Penyewa (`tenant_id`) | Ya | `required`, `exists:tenants,id` | Tenant pemilik unit |
| Nomor Unit (`unit_number`) | Ya | `required`, max 50 | Identifier unit |
| Lantai (`floor`) | Ya | `required`, max 50 | Letak lantai |
| Luas (`area_size`) | Tidak | `nullable`, `numeric` | Luas area (m²) |
| Status Aktif (`is_active`) | Ya | `required`, `boolean` | Aktif/non-aktif |
| Mulai Sewa (`lease_start`) | Tidak | `nullable`, `date` | Tanggal mulai kontrak |
| Akhir Sewa (`lease_end`) | Tidak | `nullable`, `date` | Tanggal akhir kontrak |

Submit (`POST /units`) menyimpan unit dan menampilkan pesan sukses "Unit berhasil ditambahkan."

### 3.3 Mengubah Unit

Form ubah unit (`GET /units/{unit}/edit`) memungkinkan memperbarui data unit. Aturan validasi sama dengan penambahan. Submit (`PUT /units/{unit}`) menampilkan pesan "Unit berhasil diperbarui."

### 3.4 Menghapus Unit

Tombol hapus (`DELETE /units/{unit}`) menghapus record unit. Pesan yang ditampilkan: "Unit berhasil dihapus."

> ⚠️ **Perhatian:** Hapus unit hanya bila tidak ada meter aktif yang terhubung. Untuk menonaktifkan unit tanpa menghapus data historis, ubah field `is_active` menjadi `false` (0) melalui form edit.

### 3.5 Melihat Detail Unit

Halaman `GET /units/{unit}` menampilkan informasi lengkap unit beserta data tenant pemiliknya (`$unit->load('tenant')`).

## 4. Manajemen Utility Meter

Modul Utility Meters mengelola meteran utilitas (listrik/air) yang terpasang pada setiap unit. Setiap meter terhubung ke satu unit dan dapat dikaitkan dengan satu tarif.

> **Rute:** `Route::resource('utility-meters', UtilityMeterController::class)` → `GET /utility-meters`, `GET /utility-meters/create`, `POST /utility-meters`, `GET /utility-meters/{utility_meter}`, `GET /utility-meters/{utility_meter}/edit`, `PUT /utility-meters/{utility_meter}`, `DELETE /utility-meters/{utility_meter}`.

### 4.1 Daftar Meter

Halaman `GET /utility-meters` menampilkan daftar meter dengan paginasi 10 entri per halaman. Setiap entri memuat relasi `unit` dan `tariff`. Fitur pencarian (`search`) mencari pada:

- `meter_number` (nomor meter)
- `unit_number` (nomor unit, via relasi `unit`)
- `name` tarif (nama tarif, via relasi `tariff`)

Daftar semua `units` dan `tariffs` juga dimuat untuk keperluan form tambah/edit cepat di halaman yang sama.

### 4.2 Menambah Meter

Form tambah meter (`GET /utility-meters/create`) memerlukan isian:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Unit (`unit_id`) | Ya | `required`, `exists:units,id` | Unit tempat meter terpasang |
| Jenis Meter (`meter_type`) | Ya | `required`, `in:electricity,water` | Listrik atau air |
| Nomor Meter (`meter_number`) | Ya | `required`, max 100 | Serial/ID meter |
| Daya (`power_capacity`) | Tidak | `nullable`, max 100 | Kapasitas daya (VA/kW) |
| Tarif (`tariff_id`) | Tidak | `nullable`, `exists:tariffs,id` | Tarif yang diterapkan |
| Kategori (`meter_category`) | Ya | `required`, `in:postpaid,prepaid` | Pascabayar/prabayar |

Submit (`POST /utility-meters`) menyimpan meter dan menampilkan status `meter-stored`.

### 4.3 Mengubah Meter

Form ubah meter (`GET /utility-meters/{utility_meter}/edit`) memungkinkan memperbarui data meter. Aturan validasi sama dengan penambahan, **ditambah** field:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Pengali (`multiplier`) | Ya | `required`, `numeric` | Faktor pengali pembacaan (mis. 1 untuk KWH langsung, >1 untuk CT) |

> ℹ️ **Catatan:** Field `multiplier` hanya divalidasi saat update, tidak saat store. Pastikan nilai pengali diisi saat pertama kali mengedit meter agar perhitungan tagihan akurat.

Submit (`PUT /utility-meters/{utility_meter}`) menampilkan status `meter-updated`.

### 4.4 Menghapus Meter

Tombol hapus (`DELETE /utility-meters/{utility_meter}`) menghapus record meter. Status yang ditampilkan: `meter-deleted`.

> ⚠️ **Perhatian:** Hapus meter hanya bila tidak ada pembacaan meter (meter readings) yang masih terkait, untuk menjaga integritas data historis tagihan.

### 4.5 Melihat Detail Meter

Halaman `GET /utility-meters/{utility_meter}` menampilkan informasi lengkap meter beserta data unit dan tarif terkait (`$utilityMeter->load(['unit', 'tariff'])`).

## 5. Manajemen Tarif (Tariffs)

Modul Tariffs mengelola konfigurasi tarif utilitas: harga air, harga listrik, persentase pajak, dan beragam biaya tambahan (biaya beban listrik, pemeliharaan trafo, biaya admin, biaya materai).

> **Rute:** `Route::resource('tariffs', TariffController::class)` → `GET /tariffs`, `GET /tariffs/create`, `POST /tariffs`, `GET /tariffs/{tariff}`, `GET /tariffs/{tariff}/edit`, `PUT /tariffs/{tariff}`, `DELETE /tariffs/{tariff}`.

### 5.1 Daftar Tarif

Halaman `GET /tariffs` menampilkan daftar tarif dengan paginasi 10 entri per halaman, diurutkan berdasarkan waktu pembuatan terbaru. Fitur pencarian (`search`) mencari pada kolom `name` (nama tarif).

### 5.2 Menambah Tarif

Form tambah tarif (`GET /tariffs/create`) memerlukan isian:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Nama Tarif (`name`) | Ya | `required`, max 255 | Identifier tarif |
| Harga Air (`water_price`) | Ya | `required`, `numeric` | Tarif per unit air |
| Harga Listrik (`electric_price`) | Ya | `required`, `numeric` | Tarif per unit listrik |
| Persentase Pajak (`tax_percent`) | Ya | `required`, `numeric` | Pajak (%) |
| Biaya Lain (`other_fees`) | Tidak | `nullable`, `array` | Biaya tambahan (lihat di bawah) |

#### Biaya Lain (`other_fees`)

Field `other_fees` adalah array yang dapat memuat sub-field berikut. Bila tidak diisi, default-nya `0`:

| Sub-field | Dipetakan ke kolom | Keterangan |
| --- | --- | --- |
| `electric_load` | `electric_load_cost` | Biaya beban listrik |
| `maintenance` | `transformer_maintenance` | Biaya pemeliharaan trafo |
| `admin_fee` | `admin_fee` | Biaya administrasi |
| `stamp_fee` | `stamp_fee` | Biaya materai |

Sub-field selain keempat di atas akan disimpan sebagai JSON pada kolom `other_fees` (untuk biaya kustom tambahan).

Submit (`POST /tariffs`) menyimpan tarif dan menampilkan status `tariff-stored`. Bila gagal, ditampilkan pesan error "Something went wrong."

### 5.3 Mengubah Tarif

Form ubah tarif (`GET /tariffs/{tariff}/edit`) memungkinkan memperbarui data tarif. Aturan validasi sama dengan penambahan, kecuali `tax_percent` bersifat `nullable` (dapat dikosongkan untuk menonaktifkan pajak).

Pemetaan `other_fees` ke kolom-kolom biaya tambahan berlaku sama dengan penambahan. Submit (`PUT /tariffs/{tariff}`) menampilkan status `tariff-updated`.

### 5.4 Menghapus Tarif

Tombol hapus (`DELETE /tariffs/{tariff}`) menghapus record tarif. Status yang ditampilkan: `tariff-deleted`.

> ⚠️ **Perhatian:** Menghapus tarif yang masih dikaitkan dengan meter aktif dapat menyebabkan tagihan berikutnya tidak memiliki acuan harga. Nonaktifkan atau ganti tarif pada meter terkait sebelum menghapus.

### 5.5 Melihat Detail Tarif

Halaman `GET /tariffs/{tariff}` menampilkan informasi lengkap tarif, termasuk rincian seluruh komponen biaya.

## 6. Alur Kerja Master Data yang Disarankan

Untuk onboarding tenant baru secara lengkap, ikuti urutan berikut:

1. **Buat Tarif** (jika tarif baru diperlukan) → [§5.2](#52-menambah-tarif)
2. **Buat Penyewa** (sekaligus membuat akun login tenant) → [§2.2](#22-menambah-penyewa)
3. **Buat Unit** untuk tenant tersebut → [§3.2](#32-menambah-unit)
4. **Buat Utility Meter** untuk unit, kaitkan dengan tarif → [§4.2](#42-menambah-meter)
5. **Verifikasi** akun tenant dapat login dan mengganti password default → [09-profile-notifications.md](09-profile-notifications.md)

Urutan ini memastikan seluruh relasi data terbentuk sebelum operasi pembacaan meter dan penagihan dimulai (lihat [05-operations.md](05-operations.md)).

## 7. Status Flash Message Referensi

Berikut ringkasan status flash message yang muncul setelah operasi pada modul master data:

| Modul | Operasi | Status |
| --- | --- | --- |
| Users | Tambah / Ubah / Hapus | `user-created`, `user-updated`, `user-deleted` |
| Tenants | Tambah / Ubah / Hapus | `tenant-created`, `tenant-updated`, `tenant-deleted` |
| Units | Tambah / Ubah / Hapus | "Unit berhasil ditambahkan/diperbarui/dihapus." |
| Utility Meters | Tambah / Ubah / Hapus | `meter-stored`, `meter-updated`, `meter-deleted` |
| Tariffs | Tambah / Ubah / Hapus | `tariff-stored`, `tariff-updated`, `tariff-deleted` |

## 8. Hak Akses Ringkas

| Modul | admin | supervisor | petugas | tenant |
| --- | --- | --- | --- | --- |
| Users | CRUD | Read | — | — |
| Tenants | CRUD | CRUD | Read | — |
| Units | CRUD | CRUD | Read | Read (milik sendiri) |
| Utility Meters | CRUD | CRUD | Read | Read (milik sendiri) |
| Tariffs | CRUD | Read | Read | — |

> **Catatan:** Tabel di atas mendokumentasikan akses yang dimaksud (intended). Verifikasi penerapan middleware `role` pada rute resource sebelum mengandalkan tabel ini di lingkungan produksi. Lihat `SECURITY_AUDIT_REPORT.md` untuk temuan terkait.
