# 09. Profil & Notifikasi

Modul **Profil** dan **Notifikasi** adalah fitur personal yang melekat pada setiap akun pengguna Braga8. Modul Profil memungkinkan pengguna mengelola data diri, mengganti kata sandi, serta menghapus akun. Modul Notifikasi menyampaikan pemberitahuan sistem terkait aktivitas operasional yang relevan dengan pengguna — misalnya pengingat tagihan, keluhan baru, atau pembayaran diterima — dan dapat ditandai sebagai telah dibaca atau dihapus.

> **Akses:**
>
> - Semua peran (`admin`, `supervisor`, `petugas`, `tenant`) dapat mengelola profil dan notifikasi masing-masing.
> - Setiap pengguna hanya dapat melihat dan memodifikasi profil serta notifikasi miliknya sendiri.
>
> **Rute Profil:**
>
> - `GET /profile` → `ProfileController@edit` (nama rute: `profile.edit`) — menampilkan halaman profil
> - `PATCH /profile` → `ProfileController@update` (nama rute: `profile.update`) — memperbarui data profil
> - `DELETE /profile` → `ProfileController@destroy` (nama rute: `profile.destroy`) — menghapus akun
>
> **Rute Notifikasi:**
>
> - `GET /notifications` → mengarahkan kembali ke `dashboard`
> - `POST /notifications/{notification}/read` → `NotificationController@markAsRead` (nama rute: `notifications.read`) — menandai satu notifikasi sebagai telah dibaca
> - `DELETE /notifications/{notification}` → `NotificationController@destroy` (nama rute: `notifications.destroy`) — menghapus satu notifikasi
> - `DELETE /notifications` → `NotificationController@destroyAll` (nama rute: `notifications.destroyAll`) — menghapus seluruh notifikasi pengguna

---

## 09.1 Mengelola Profil Pengguna

Halaman profil dapat dibuka dari menu **Profil** (umumnya di pojok kanan atas, pada dropdown akun pengguna). Halaman ini terbagi menjadi tiga bagian: **Informasi Profil**, **Kata Sandi**, dan **Hapus Akun**.

### 09.1.1 Memperbarui Informasi Profil

Bagian **Informasi Profil** digunakan untuk mengubah nama dan alamat email.

| Bidang | Keterangan |
| -------- | ------------ |
| **Nama** | Nama lengkap pengguna. Wajib diisi, maksimum 255 karakter. |
| **Email** | Alamat email unik. Wajib diisi, format email valid, maksimum 255 karakter. Email harus belum digunakan oleh pengguna lain. |

**Langkah memperbarui:**

1. Buka halaman **Profil**.
2. Pada kartu **Informasi Profil**, ubah **Nama** dan/atau **Email**.
3. Klik tombol **Simpan**.
4. Jika validasi berhasil, data tersimpan dan pesan sukses ditampilkan.
5. Jika email berubah, sistem akan memperbarui email aktif. Pastikan email baru dapat diakses karena email ini digunakan untuk notifikasi dan pemulihan akun.

**Aturan validasi (ProfileUpdateRequest):**

| Bidang | Aturan |
| -------- | -------- |
| `name` | `required\|string\|max:255` |
| `email` | `required\|email\|max:255\|unique:users,email,{id_pengguna}` |
| `current_password` | `nullable\|required_with:password\|string` |
| `password` | `nullable\|string\|min:8\|confirmed` |

> Catatan: Bidang kata sandi (`current_password`, `password`, `password_confirmation`) divalidasi dalam form yang sama, namun pembaruan kata sandi dijelaskan terpisah pada subbagian 09.1.2 agar lebih jelas.

### 09.1.2 Mengganti Kata Sandi

Bagian **Kata Sandi** memungkinkan pengguna mengganti password tanpa keluar dari sesi.

| Bidang | Keterangan |
| -------- | ------------ |
| **Kata Sandi Saat Ini** | Password lama yang sedang aktif. Wajib diisi jika ingin mengganti password. |
| **Kata Sandi Baru** | Password baru, minimal 8 karakter. |
| **Konfirmasi Kata Sandi** | Harus sama dengan Kata Sandi Baru. |

**Langkah mengganti kata sandi:**

1. Buka halaman **Profil**, lalu gulir ke kartu **Kata Sandi**.
2. Masukkan **Kata Sandi Saat Ini**.
3. Masukkan **Kata Sandi Baru** dan **Konfirmasi Kata Sandi**.
4. Klik tombol **Simpan**.
5. Jika kata sandi lama benar dan konfirmasi cocok, kata sandi diperbarui. Pengguna tetap berada dalam sesi — tidak perlu login ulang.

> Keamanan: Kata sandi lama diverifikasi terhadap hash di database. Jika salah, sistem menolak perubahan dan menampilkan pesan kesalahan tanpa mengungkapkan kata sandi yang benar.

### 09.1.3 Menghapus Akun

Bagian **Hapus Akun** bersifat permanen dan tidak dapat dibatalkan.

**Langkah menghapus akun:**

1. Buka halaman **Profil**, lalu gulir ke kartu **Hapus Akun**.
2. Klik tombol **Hapus Akun**.
3. Sistem akan meminta konfirmasi — pastikan Anda yakin karena seluruh data terkait akun akan dihapus.
4. Setelah konfirmasi, `ProfileController@destroy` akan:
   - Memanggil `Auth::logout()` untuk mengakhiri sesi.
   - Menghapus record pengguna dari database (`$user->delete()`).
   - Menginvalidasi sesi (`Session::invalidate()`, `Session::regenerateToken()`).
5. Pengguna diarahkan kembali ke halaman login.

> Peringatan: Tindakan ini **tidak dapat dibatalkan**. Pastikan tidak ada data operasional yang masih membutuhkan akses akun ini sebelum melanjutkan. Hubungi admin sistem jika akun perlu dinonaktifkan tanpa dihapus.

---

## 09.2 Notifikasi

Notifikasi adalah pesan singkat yang dibuat sistem ketika terjadi peristiwa operasional tertentu. Notifikasi ditampilkan pada ikon lonceng di bilah menu atas dan dapat dikelola melalui dropdown atau halaman terkait.

### 09.2.1 Sumber Notifikasi

Notifikasi dibuat secara otomatis oleh berbagai komponen sistem:

| Sumber | Pemicu | Contoh Pesan |
| -------- | -------- | -------------- |
| **ReminderController** | Pengingat tagihan dibuat/diperbarui | *"Pengingat tagihan baru telah dibuat untuk INV-2026-001"* |
| **InvoiceController** | Invoice diterbitkan | *"Tagihan baru INV-2026-001 telah diterbitkan"* |
| **ComplaintController** | Keluhan dibuat/diperbarui | *"Keluhan baru #CMP-001 telah diterima"* |
| **PaymentController** | Pembayaran dicatat | *"Pembayaran untuk INV-2026-001 telah diterima"* |
| **SendPaymentReminders** (command) | Pengingat pembayaran otomatis terjadwal | *"Pengingat: tagihan Anda akan jatuh tempo"* |
| **SendReminderNotifications** (command) | Notifikasi pengingat otomatis | *"Pengingat otomatis untuk tagihan tertunggak"* |
| **Api/NotificationController** | Notifikasi via API (integrasi) | Notifikasi dari sistem eksternal |
| **Api/ComplaintController** | Keluhan via API | Notifikasi keluhan dari kanal mobile/API |

> Notifikasi bersifat **per-pengguna**. Setiap pengguna hanya melihat notifikasi yang ditujukan untuknya melalui relasi `customNotifications()` pada model `User`.

### 09.2.2 Membaca Notifikasi

Notifikasi baru ditandai dengan ikon lonceng yang menampilkan **badge jumlah** notifikasi belum dibaca.

**Langkah membaca notifikasi:**

1. Klik ikon lonceng di bilah menu atas.
2. Dropdown menampilkan daftar notifikasi terbaru.
3. Setiap notifikasi menampilkan:
   - **Pesan** — deskripsi singkat peristiwa.
   - **Waktu** — kapan notifikasi dibuat.
   - **Status** — belum dibaca (ditandai dengan titik/penyorotan) atau sudah dibaca.
4. Untuk menandai sebuah notifikasi sebagai telah dibaca, klik tombol/tautan **Tandai Dibaca** pada notifikasi tersebut.
   - Form mengirim `POST /notifications/{id}/read` ke `NotificationController@markAsRead`.
   - Kolom `is_read` di tabel `notifications` diubah menjadi `true`.
   - Badge jumlah akan diperbarui.

### 09.2.3 Menghapus Satu Notifikasi

Pengguna dapat menghapus notifikasi tertentu yang sudah tidak diperlukan.

**Langkah:**

1. Buka dropdown notifikasi (ikon lonceng).
2. Pada notifikasi yang ingin dihapus, klik tombol/ikon **Hapus**.
3. Form mengirim `DELETE /notifications/{id}` ke `NotificationController@destroy`.
4. Notifikasi dihapus permanen dari database.

> Penghapusan bersifat permanen. Pastikan notifikasi tidak lagi diperlukan sebelum dihapus.

### 09.2.4 Menghapus Semua Notifikasi

Untuk membersihkan seluruh notifikasi sekaligus:

1. Buka dropdown notifikasi.
2. Klik tombol **Hapus Semua** (jika tersedia di bagian bawah dropdown).
3. Form mengirim `DELETE /notifications` ke `NotificationController@destroyAll`.
4. Seluruh notifikasi milik pengguna yang sedang login dihapus dari database.

> Tindakan ini menghapus **semua** notifikasi — baik yang sudah dibaca maupun belum dibaca — dan tidak dapat dibatalkan.

---

## 09.3 Atribut Notifikasi

Setiap notifikasi tersimpan sebagai record pada tabel `notifications` dengan atribut berikut:

| Atribut | Tipe | Keterangan |
| --------- | ------ | ------------ |
| `id` | bigint | Pengenal unik notifikasi |
| `user_id` | bigint | ID pengguna penerima notifikasi |
| `title` | string | Judul singkat notifikasi |
| `message` | text | Isi pesan notifikasi |
| `type` | string | Kategori/jenis notifikasi (mis. `invoice`, `complaint`, `payment`, `reminder`) |
| `is_read` | boolean | `false` = belum dibaca, `true` = sudah dibaca |
| `created_at` | timestamp | Waktu notifikasi dibuat |
| `updated_at` | timestamp | Waktu terakhir diperbarui |

Relasi: setiap `Notification` dimiliki oleh satu `User` (`belongsTo`), dan setiap `User` dapat memiliki banyak `Notification`.

---

## 09.4 Tips Penggunaan

1. **Periksa notifikasi secara berkala** — ikon lonceng menampilkan jumlah belum dibaca; jangan biarkan menumpuk agar tidak ada info penting yang terlewat.
2. **Tandai dibaca setelah ditindaklanjuti** — agar badge kembali ke nol dan notifikasi baru lebih mudah terlihat.
3. **Hapus notifikasi lama** — gunakan **Hapus Semua** setelah seluruh notifikasi ditindaklanjuti untuk menjaga daftar tetap ringkas.
4. **Jaga keamanan kata sandi** — ganti kata sandi secara berkala (mis. setiap 90 hari) dan gunakan kombinasi huruf, angka, serta simbol.
5. **Perbarui email aktif** — pastikan email pada profil masih dapat diakses, karena email ini digunakan untuk komunikasi sistem.
6. **Hindari berbagi akun** — setiap pengguna wajib memiliki akun sendiri agar jejak audit dan notifikasi tetap akurat per individu.

---

## 09.5 Batasan & Catatan

- **Tidak ada notifikasi push email otomatis** — notifikasi hanya tersaji di dalam aplikasi (ikon lonceng). Pengguna harus login untuk melihatnya.
- **Tidak dapat membalas notifikasi** — notifikasi bersifat satu arah (sistem → pengguna). Untuk menindaklanjuti, gunakan modul terkait (mis. modul Keluhan untuk notifikasi keluhan, modul Tagihan untuk notifikasi invoice).
- **Tidak ada filter/kategori di UI** — seluruh notifikasi tampil dalam satu daftar kronologis. Gunakan judul/pesan untuk membedakan jenis.
- **Penghapusan akun bersifat permanen** — tidak ada mekanisme pemulihan. Admin sistem tidak dapat membatalkan penghapusan yang sudah dieksekusi.
- **Penghapusan notifikasi bersifat permanen** — tidak ada fitur "sampah" atau pemulihan notifikasi yang sudah dihapus.
- **Akses `GET /notifications`** — rute ini hanya mengarahkan kembali ke dashboard; tidak ada halaman khusus daftar notifikasi. Pengelolaan notifikasi dilakukan melalui dropdown pada bilah menu.
