# Dashboard

Modul Dashboard merupakan halaman utama yang ditampilkan kepada pengguna setelah login. Halaman ini memberikan ringkasan operasional Braga8 Utility Billing secara real-time, mencakup metrik keuangan, status pembayaran, progres input meter, keluhan aktif, dan grafik pemakaian utilitas bulanan.

> **Akses:** Semua peran (`admin`, `supervisor`, `petugas`, `tenant`) dapat mengakses dashboard. Rute: `GET /dashboard` (`dashboard`).
>
> **Catatan Keamanan:** Tidak ada pembatasan data berdasarkan peran pada dashboard—semua pengguna melihat metrik agregat yang sama. Lihat catatan BOLA pada `docs/user/README.md`.

## 1. Tampilan Header

Bagian header dashboard menampilkan:

| Elemen | Deskripsi |
| --- | --- |
| Judul halaman | "Dashboard" dengan subjudul "Braga8 Utility Billing Management". |
| Ikon Lonceng Notifikasi | Menampilkan titik merah (notification dot) bila terdapat notifikasi yang belum dibaca. Klik untuk membuka popup notifikasi. |
| Ikon Profil | Menampilkan inisial/ikon pengguna. Klik untuk membuka popup detail profil singkat. |

Notifikasi yang belum dibaca dihitung melalui relasi `customNotifications()` pada model `User` dengan kondisi `read_at IS NULL`.

## 2. Kartu Metrik Utama (Baris Atas)

Empat kartu metrik ditampilkan pada baris pertama:

### 2.1 Total Pembayaran

- **Label:** "Total Pembayaran"
- **Nilai:** Akumulasi nominal seluruh invoice berstatus `paid` (Rp).
- **Sub-info:** Jumlah transaksi pembayaran (`paidCount`).
- **Indikator warna:** Hijau (emerald).

### 2.2 Tagihan Tertunda

- **Label:** "Tagihan Tertunda"
- **Nilai:** Akumulasi nominal seluruh invoice berstatus `unpaid` (Rp).
- **Sub-info:** Jumlah invoice belum dibayar (`unpaidCount`).
- **Indikator warna:** Merah (rose).

### 2.3 Jumlah Penyewa

- **Label:** "Jumlah Penyewa"
- **Nilai:** Total tenant terdaftar (`totalTenants`).
- **Sub-info:** Jumlah tenant baru pada bulan berjalan (`newTenantsThisMonth`), ditampilkan dengan prefiks `+`.

### 2.4 Keluhan

- **Label:** "Keluhan"
- **Nilai:** Total keluhan yang belum diselesaikan (`totalComplaints`).
- **Catatan:** Hanya keluhan berstatus `unresolved` yang dihitung.

## 3. Ringkasan Tagihan (Pie Chart)

Kartu "Ringkasan Tagihan" menampilkan diagram lingkaran (pie chart) yang merepresentasikan komposisi status invoice:

| Segmen | Warna | Sumber Data |
| --- | --- | --- |
| Lunas (Paid) | Hijau | `percentPaid` |
| Belum Dibayar (Unpaid) | Merah | `percentUnpaid` |
| Terlambat (Overdue) | Oranye | `percentOverdue` |

Setiap segmen dilengkapi persentase numerik di samping diagram. Persentase dihitung dari total invoice yang diterbitkan.

## 4. Input Meter Bulan Ini

Kartu ini memantau progres pembacaan meter untuk bulan berjalan:

| Elemen | Deskripsi |
| --- | --- |
| Jumlah meter terbaca | `metersDone` / `totalMeters` |
| Unit selesai | Jumlah unit yang memiliki minimal 2 meter reading pada bulan berjalan (`unitsCompleted`) |
| Progress bar | Visualisasi persentase `metersDone / totalMeters * 100` |
| Tombol "Log Audit" | Tautan ke halaman Audit Logs (`audit_logs.index`) |

Progress bar memudahkan supervisor/admin memantau apakah target pembacaan meter bulanan tercapai sebelum akhir periode.

## 5. Grafik Pemakaian Bulanan (Bar Chart)

Kartu "Grafik Pemakaian Bulanan" menampilkan diagram batang ganda untuk 6 bulan terakhir:

- **Sumbu X:** Nama bulan.
- **Sumbu Y:** Nilai pemakaian (skala relatif terhadap nilai maksimum).
- **Batang Listrik (kWh):** Oranye, ditandai ikon ⚡.
- **Batang Air (m³):** Biru, ditandai ikon 💧.
- **Tooltip hover:** Menampilkan nilai numerik aktual untuk setiap batang.

Data diambil dari `chartData` yang dihitung di `DashboardController` berdasarkan laporan pemakaian 6 bulan terakhir.

## 6. Tabel Pemakaian Bulanan

Di bawah grafik terdapat tabel rincian pemakaian:

| Kolom | Deskripsi |
| --- | --- |
| Bulan | Nama bulan periode. |
| Pemakaian Listrik | Nilai dalam kWh (format ribuan dengan pemisah `.`). |
| Pemakaian Air | Nilai dalam liter (L). |

Tabel menampilkan pesan "Belum ada laporan penggunaan untuk periode ini." bila `chartData` kosong.

## 7. Akses Cepat

Kartu "Akses Cepat" menyediakan tiga tombol shortcut operasional:

| Tombol | Tujuan Rute | Fungsi |
| --- | --- | --- |
| Buat Tagihan | `invoices.index` | Membuka halaman daftar invoice untuk membuat tagihan baru. |
| Tambah Penyewa | `tenants.index` | Membuka halaman daftar tenant untuk menambah penyewa baru. |
| Ubah Tarif | `tariffs.index` | Membuka halaman daftar tarif untuk mengubah tarif. |

## 8. Alert Status Sesi

Bila terdapat flash session `status`, dashboard menampilkan banner notifikasi di pojok kanan atas. Contoh:

- **`profile-updated`:** "Profil Diperbarui! Informasi akun kamu sudah berhasil diubah."

Banner otomatis hilang setelah 4,5 detik atau dapat ditutup manual melalui tombol `×`.

## 9. Sumber Data

Seluruh metrik dashboard dihitung di `App\Http\Controllers\DashboardController` melalui query agregat pada model:

- `Tenant` — total dan tenant baru bulan ini.
- `Invoice` — total nilai, status pembayaran, jumlah overdue.
- `UtilityMeter` — total meter dan meter terbaca bulan ini.
- `MeterReading` — unit yang selesai dibaca.
- `Complaint` — keluhan belum terselesaikan.
- `Report` — data pemakaian listrik & air 6 bulan terakhir untuk grafik.

## 10. Hak Akses per Peran

| Peran | Akses Dashboard | Catatan |
| --- | --- | --- |
| `admin` | Penuh | Melihat seluruh metrik agregat. |
| `supervisor` | Penuh | Melihat seluruh metrik agregat. |
| `petugas` | Penuh | Melihat seluruh metrik agregat. |
| `tenant` | Penuh | Melihat seluruh metrik agregat (lihat catatan BOLA). |

> **Peringatan Keamanan:** Dashboard saat ini tidak melakukan filter data berdasarkan peran. Tenant dapat melihat metrik agregat seluruh organisasi (total pembayaran, jumlah penyewa, dll.). Hal ini termasuk dalam kategori BOLA (Broken Object Level Authorization) yang didokumentasikan pada `SECURITY_AUDIT_REPORT.md`. Rekomendasi: implementasikan scope data berdasarkan peran pada `DashboardController`.
