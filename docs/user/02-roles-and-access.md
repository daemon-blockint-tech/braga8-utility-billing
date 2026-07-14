# 02 — Peran dan Hak Akses (Roles & Access)

Braga8 Utility Billing mengimplementasikan kontrol akses berbasis peran (Role-Based
Access Control / RBAC) dengan empat peran utama. Dokumen ini menjelaskan setiap peran,
hak aksesnya pada antarmuka web dan API, serta **catatan keamanan penting** mengenai
kerentanan BOLA (Broken Object Level Authorization) yang telah diidentifikasi.

---

## 2.1 Gambaran Umum RBAC

Sistem menggunakan middleware `App\Http\Middleware\CheckRole` untuk membatasi akses
rute berdasarkan peran pengguna. Peran disimpan pada kolom `role` di tabel `users`.

| Peran | Kode | Deskripsi Singkat |
| ------- | ------ | ------------------- |
| Administrator | `admin` | Akses penuh ke semua modul dan konfigurasi sistem |
| Supervisor | `supervisor` | Pengawasan operasional, laporan, dan manajemen pengguna terbatas |
| Petugas | `petugas` | Eksekusi harian: baca meter, kelola tagihan, terima pembayaran |
| Penyewa | `tenant` | Akses terbatas ke data miliknya sendiri: tagihan, pembayaran, keluhan |

> Middleware: `CheckRole::class` dipasang pada grup rute `auth` di `routes/web.php`.
> Pemeriksaan peran juga dilakukan di dalam beberapa controller (lihat catatan
> keamanan di §2.5).

---

## 2.2 Matriks Akses Modul Web

Tabel berikut menunjukkan modul yang dapat diakses tiap peran pada antarmuka web.

| Modul | admin | supervisor | petugas | tenant |
| ------- | :-----: | :----------: | :-------: | :------: |
| Dashboard | ✅ | ✅ | ✅ | ✅ (terbatas) |
| Profile | ✅ | ✅ | ✅ | ✅ |
| Manajemen Pengguna | ✅ | ✅ (terbatas) | ❌ | ❌ |
| Tenants (Penyewa) | ✅ | ✅ | ✅ (baca) | ❌ |
| Units | ✅ | ✅ | ✅ (baca) | ❌ |
| Utility Meters | ✅ | ✅ | ✅ | ❌ |
| Meter Readings | ✅ | ✅ | ✅ | ❌ |
| Tariffs | ✅ | ✅ | ❌ | ❌ |
| Invoices | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Reminders | ✅ | ✅ | ✅ | ❌ |
| Payments | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Complaints | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Usage Reports | ✅ | ✅ | ❌ | ❌ |
| Audit Logs | ✅ | ✅ | ❌ | ❌ |
| Notifications | ✅ | ✅ | ✅ | ✅ |

Keterangan:

- ✅ = akses penuh (CRUD sesuai modul)
- ✅ (terbatas) = akses sebagian (lihat catatan per peran di §2.3)
- ✅ (miliknya) = hanya data milik pengguna itu sendiri
- ❌ = tidak ada akses

---

## 2.3 Detail Per Peran

### 2.3.1 Administrator (`admin`)

**Tanggung Jawab:**

- Konfigurasi sistem dan manajemen pengguna
- Pengaturan tarif dan kebijakan penagihan
- Pengawasan penuh audit log dan laporan
- Manajemen seluruh data master (penyewa, unit, meter, tarif)

**Hak Akses Web:**

- Semua modul tersedia di sidebar
- Dapat membuat, mengubah, menghapus semua entitas
- Dapat melihat dan ekspor audit log
- Dapat mengelola role pengguna lain

**Hak Akses API:**

- Endpoint `/api/*` tersedia penuh
- Dapat melakukan operasi CRUD pada semua resource

### 2.3.2 Supervisor (`supervisor`)

**Tanggung Jawab:**

- Pengawasan operasional harian
- Validasi meter reading dan approval tagihan
- Manajemen pengguna terbatas (petugas & tenant)
- Analisis laporan pemakaian

**Hak Akses Web:**

- Sebagian besar modul operasional
- Manajemen pengguna: dapat membuat/mengubah akun `petugas` dan `tenant`

  (tidak dapat membuat akun `admin` lain)

- Akses penuh ke laporan dan audit log
- Tidak dapat mengubah tarif (hanya baca) — tergantung konfigurasi

**Hak Akses API:**

- Endpoint operasional tersedia
- Tidak dapat mengelola akun admin

### 2.3.3 Petugas (`petugas`)

**Tanggung Jawab:**

- Pencatatan meter reading harian
- Pembuatan dan pengiriman tagihan
- Penerimaan pembayaran
- Penanganan keluhan tingkat pertama

**Hak Akses Web:**

- Dashboard operasional
- Baca data penyewa dan unit (tidak dapat mengubah)
- CRUD meter reading
- CRUD invoices dan reminders
- CRUD payments
- CRUD complaints
- Tidak dapat melihat tarif, laporan, atau audit log

**Hak Akses API:**

- Endpoint operasional (meter-readings, invoices, payments, complaints)
- Tidak dapat mengakses endpoint admin/supervisor

### 2.3.4 Penyewa (`tenant`)

**Tanggung Jawab:**

- Melihat tagihan miliknya
- Melakukan pembayaran
- Mengajukan dan melacak keluhan
- Memperbarui profil pribadi

**Hak Akses Web:**

- Dashboard terbatas (ringkasan tagihan miliknya)
- Daftar invoice miliknya saja
- Riwayat pembayaran miliknya
- Keluhan yang diajukan sendiri
- Profil dan notifikasi

**Hak Akses API:**

- Hanya endpoint yang mengembalikan data miliknya
- Tidak dapat melihat data penyewa lain

---

## 2.4 Akses API

Selain antarmuka web, Braga8 menyediakan API berbasis REST (rute `routes/api.php`)
untuk integrasi sistem eksternal. Akses API menggunakan token otentikasi (Sanctum).

| Grup Endpoint | admin | supervisor | petugas | tenant |
| --------------- | :-----: | :----------: | :-------: | :------: |
| `/api/users` | ✅ | ✅ (terbatas) | ❌ | ❌ |
| `/api/tenants` | ✅ | ✅ | ✅ (baca) | ❌ |
| `/api/units` | ✅ | ✅ | ✅ (baca) | ❌ |
| `/api/utility-meters` | ✅ | ✅ | ✅ | ❌ |
| `/api/meter-readings` | ✅ | ✅ | ✅ | ❌ |
| `/api/tariffs` | ✅ | ✅ | ❌ | ❌ |
| `/api/invoices` | ✅ | ✅ | ✅ | ✅ (miliknya) |
| `/api/payments` | ✅ | ✅ | ✅ | ✅ (miliknya) |
| `/api/complaints` | ✅ | ✅ | ✅ | ✅ (miliknya) |
| `/api/reports` | ✅ | ✅ | ❌ | ❌ |

> Detail endpoint per modul dijelaskan pada dokumen modul terkait
> (`04-master-data.md`, `05-operations.md`, dst.).

---

## 2.5 ⚠️ Catatan Keamanan: Kerentanan BOLA

### Apa itu BOLA?

**Broken Object Level Authorization (BOLA)** — juga dikenal sebagai IDOR (Insecure
Direct Object Reference) — adalah kerentanan di mana pengguna dapat mengakses,
mengubah, atau menghapus objek (resource) milik pengguna lain dengan memanipulasi
identifier (ID) pada permintaan, meskipun peran mereka seharusnya tidak
memperbolehkan.

### Status di Braga8 Utility Billing

Audit keamanan internal (`SECURITY_AUDIT_REPORT.md`) telah mengidentifikasi
**kerentanan BOLA sistemik** pada aplikasi ini. Akar masalah:

1. **Pemeriksaan peran dilakukan di dalam controller**, bukan di middleware.

   Beberapa controller memeriksa `auth()->user()->role` di awal method, tetapi
   tidak selalu mengaitkan objek yang diakses dengan pemilik yang sah.

2. **Tidak ada scope otomatis per pengguna** pada model `Invoice`, `Payment`,

   `Complaint`, dan `MeterReading`. Sebuah `tenant` dapat mengakses
   `/invoices/5` milik tenant lain hanya dengan menebak ID.

3. **API endpoint tidak konsisten** dalam menerapkan kebijakan kepemilikan.

### Dampak Praktis

| Peran Berbahaya | Dampak Potensial |
| ----------------- | ------------------ |
| `tenant` | Membaca tagihan, pembayaran, dan keluhan penyewa lain |
| `petugas` | Mengubah meter reading di luar wilayah tugasnya |
| `supervisor` | Mengakses data di luar lingkup pengawasan |

### Rekomendasi Mitigasi (untuk admin/dev)

1. **Pindahkan pemeriksaan peran ke middleware** — gunakan `CheckRole` secara

   konsisten pada setiap grup rute, bukan inline di controller.

2. **Terapkan kebijakan otorisasi per objek** — gunakan Laravel Policy

   (`php artisan make:policy`) untuk memvalidasi kepemilikan sebelum
   setiap operasi show/update/destroy.

3. **Scope query per pengguna** — pada controller, filter query berdasarkan

   `tenant_id` dari pengguna login untuk role `tenant`.

4. **Gunakan UUID alih-alih ID auto-increment** untuk mengurangi kemudahan

   enumerasi.

5. **Tambahkan test otorisasi** — verifikasi bahwa pengguna A tidak dapat

   mengakses resource milik pengguna B.

> Lihat `SECURITY_AUDIT_REPORT.md` untuk detail lengkap temuan dan rekomendasi.
> Pengguna akhir tidak perlu tindakan teknis — namun **admin wajib membatasi
> penyebaran akun `tenant`** sampai mitigasi diterapkan.

### Apa yang Harus Dilakukan Pengguna?

- **Admin:** Batasi pembuatan akun `tenant` hingga patch keamanan dirilis.

  Monitor audit log untuk aktivitas mencurigakan (akses berlebih ke ID
  berbeda).

- **Supervisor:** Tinjau secara berkala apakah petugas mengakses data di luar

  tugasnya via audit log.

- **Petugas & Tenant:** Tidak ada tindakan teknis. Laporkan jika menemukan

  dapat melihat data yang seharusnya tidak terlihat.

---

## 2.6 Cara Memeriksa Peran Anda

1. Login ke aplikasi.
2. Klik avatar/inisial pada bilah atas → pop-up **Rincian Akun** muncul.
3. Field **Role** menampilkan peran Anda (`admin` / `supervisor` / `petugas` /

   `tenant`).

4. Menu sidebar yang Anda lihat mencerminkan peran tersebut.

Jika peran tidak sesuai tugas Anda, hubungi administrator sistem.

---

## 2.7 Perubahan Peran

Hanya `admin` yang dapat mengubah peran pengguna:

1. Buka **Manajemen Pengguna** → **Users** (lihat `04-master-data.md`).
2. Pilih pengguna → **Edit**.
3. Ubah field **Role**.
4. Simpan.

> Perubahan peran tercatat di audit log (lihat `08-audit-logs.md`).

---

## 2.8 Langkah Berikutnya

- `03-dashboard.md` — mengenal dasbor dan widget ringkasan.
- `04-master-data.md` — manajemen data master (pengguna, penyewa, unit, meter, tarif).
