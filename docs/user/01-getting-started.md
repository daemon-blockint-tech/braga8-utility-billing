# 01 — Memulai (Getting Started)

Panduan ini menjelaskan langkah pertama untuk mengakses Braga8 Utility Billing:
membuka aplikasi, mendaftar akun, masuk (login), verifikasi email, lupa password,
dan berinteraksi dengan navigasi umum.

---

## 1.1 Persyaratan Sistem

| Komponen | Persyaratan |
| ---------- | ------------- |
| Browser | Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi terbaru |
| JavaScript | Aktif |
| Cookies | Aktif (sesi login memerlukan cookie) |
| Koneksi | Internet stabil; aplikasi di-host pada server web |
| Resolusi | Minimal 1280×720 (desktop). Tampilan responsif tersedia untuk tablet. |

Aplikasi tidak mendukung Internet Explorer.

---

## 1.2 Membuka Aplikasi

1. Buka browser.
2. Arahkan ke URL aplikasi (contoh: `https://braga8.example.app`).
3. Halaman beranda komersial (`/`) ditampilkan — berisi informasi umum produk

   Braga8 Utility Billing.

4. Klik **Login** pada bilah navigasi atas untuk masuk ke dasbor internal.

> Halaman beranda (`/`) bersifat publik dan dapat diakses tanpa akun.
> Semua fitur internal berada di balik otentikasi.

---

## 1.3 Mendaftar Akun Baru

Pendaftaran akun baru dilakukan oleh pengguna awal melalui halaman registrasi.
Pada operasional normal, admin/supervisor membuatkan akun untuk petugas dan
penyewa melalui modul **Manajemen Pengguna** (lihat `04-master-data.md`).

1. Klik **Register** pada bilah navigasi (hanya muncul untuk pengguna belum login).
2. Isi formulir:
   - **Name** — nama lengkap
   - **Email** — alamat email aktif (akan diverifikasi)
   - **Password** — minimal 8 karakter
   - **Confirm Password** — ulangi password
3. Klik **Register**.
4. Sistem membuat akun baru dan mengirim email verifikasi.
5. Akun belum dapat mengakses dasbor sampai email diverifikasi (lihat 1.5).

> Rute: `GET /register` (form), `POST /register` (submit).
> Controller: `App\Http\Controllers\Auth\RegisteredUserController`.

---

## 1.4 Masuk (Login)

1. Klik **Login** pada bilah navigasi atas.
2. Isi:
   - **Email** — email terdaftar
   - **Password** — kata sandi akun
3. (Opsional) Centang **Remember me** untuk tetap masuk selama 30 hari.
4. Klik **Log in**.

**Jika berhasil:**

- Pengguna yang sudah verifikasi email diarahkan ke `/dashboard`.
- Pengguna yang belum verifikasi diarahkan ke halaman `/verify-email`

  (lihat 1.5).

**Jika gagal:**

- Pesan error ditampilkan: *"These credentials do not match our records."*
- Periksa kembali email dan password, atau gunakan fitur Lupa Password (1.6).

> Rute: `GET /login`, `POST /login`.
> Middleware: `guest` (hanya untuk pengguna belum terotentikasi).

---

## 1.5 Verifikasi Email

Setelah login pertama, pengguna diarahkan ke halaman **Verify Your Email Address**.

1. Email verifikasi otomatis dikirim ke alamat email yang didaftarkan.
2. Buka email dari Braga8 Utility Billing.
3. Klik tautan verifikasi di dalam email.
4. Browser membuka `verify-email/{id}/{hash}` dan menandai email sebagai terverifikasi.
5. Pengguna diarahkan ke `/dashboard`.

**Tautan tidak diterima?**

- Pada halaman verifikasi, klik **Click here to request another**.
- Sistem mengirim ulang email verifikasi.
- Batas rate berlaku untuk mencegah penyalahgunaan.

> Rute: `GET /verify-email`, `GET /verify-email/{id}/{hash}`,
> `POST /email/verification-notification`.
> Middleware: `auth` + `verified` (untuk fitur internal).

---

## 1.6 Lupa Password

1. Pada halaman login, klik **Forgot your password?**.
2. Masukkan alamat email akun.
3. Klik **Send Password Reset Link**.
4. Email berisi tautan reset dikirim ke alamat tersebut.
5. Klik tautan di email → halaman **Reset Password**.
6. Masukkan password baru dan konfirmasi.
7. Klik **Reset Password**.
8. Login ulang dengan password baru.

> Rute: `GET /forgot-password`, `POST /forgot-password`,
> `GET /reset-password/{token}`, `POST /reset-password`.

---

## 1.7 Navigasi Umum

Setelah login, pengguna melihat dasbor dan bilah navigasi yang terdiri dari:

### Bilah Atas (Top Bar)

- **Logo Braga8** — klik untuk kembali ke beranda publik.
- **Menu navigasi utama** — Dashboard, Profile, Log Out (lihat daftar lengkap di

  sidebar sebelah kiri untuk modul operasional).

- **Avatar / Inisial pengguna** — klik untuk membuka pop-up **Rincian Akun**.

### Pop-up Rincian Akun

Menampilkan:

- Nama lengkap
- Username
- Tanggal bergabung (join date)
- Role (admin / supervisor / petugas / tenant)
- Email
- Tombol **Edit Akun** dan **Logout**

### Edit & Perbarui Akun

Klik **Edit Akun** pada pop-up Rincian Akun:

- **Name** — ubah nama tampilan
- **Email** — ubah email (perlu verifikasi ulang)
- **Current Password** — wajib diisi untuk menyimpan perubahan
- Klik **Update** untuk menyimpan.

> Untuk mengubah password saja, gunakan halaman **Profile** (lihat
> `09-profile-notifications.md`).

### Sidebar Kiri (Modul Operasional)

Tampil menurut role. Menu yang tersedia:

| Grup Menu | Modul |
| ----------- | ------- |
| Dashboard | Ringkasan metrik |
| Penyewa & Unit | Tenants, Units |
| Utilitas | Utility Meters, Meter Readings |
| Tarif & Tagihan | Tariffs, Invoices, Reminders |
| Pembayaran | Payments |
| Laporan Pemakaian | Usage Reports |
| Keluhan | Complaints |
| Manajemen Pengguna | Users (admin/supervisor) |
| Log Audit | Audit Logs (admin/supervisor) |
| Siklus Penagihan | Reminders cycle |

Menu yang tidak tersedia untuk role tertentu disembunyikan otomatis.
Lihat `02-roles-and-access.md` untuk matriks lengkap.

### Logout

- Klik **Log Out** pada bilah atas, atau
- Klik **Logout** pada pop-up Rincian Akun.
- Sesi dihancurkan dan pengguna kembali ke halaman publik.

> Rute: `POST /logout`.

---

## 1.8 Bahasa & Konvensi

- Bahasa antarmuka: **Bahasa Indonesia** (dengan istilah teknis Inggris bila perlu).
- Format tanggal: `DD/MM/YYYY` (mis. `31/03/2026`).
- Format mata uang: `Rp` (Rupiah), contoh `Rp 150.000`.
- Ikon pada panduan:
  - ⚙️ → pengaturan/konfigurasi
  - ✅ → tindakan berhasil
  - ⚠️ → peringatan
  - 🔒 → hak akses terbatas

---

## 1.9 Pemecahan Masalah Umum

| Gejala | Penyebab Umum | Solusi |
| -------- | --------------- | -------- |
| Tidak bisa login | Email belum diverifikasi | Buka email verifikasi, klik tautan |
| Tidak bisa login | Password salah | Gunakan Lupa Password (1.6) |
| Halaman 403 / tidak ada akses | Role tidak memiliki izin | Hubungi admin untuk penugasan role |
| Email verifikasi tidak datang | Masuk folder spam | Periksa spam, atau minta kirim ulang (1.5) |
| Sesi tiba-tiba berakhir | Cookie kedaluwarsa | Login ulang |
| Tampilan rusak | Cache browser | Hard refresh (Ctrl+Shift+R) |

---

## 1.10 Langkah Berikutnya

Setelah berhasil login dan memverifikasi email, lanjutkan ke:

- `02-roles-and-access.md` — memahami peran dan hak akses Anda.
- `03-dashboard.md` — mengenal dasbor dan widget ringkasan.
