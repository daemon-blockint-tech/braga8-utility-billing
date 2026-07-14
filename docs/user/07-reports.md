# Laporan (Reports)

Modul Laporan menyediakan ringkasan statistik operasional dan keuangan dari seluruh aktivitas tagihan utilitas. Terdapat dua sumber laporan: **Dashboard** (statistik real-time) dan **Laporan Pemakaian Bulanan** (Usage Reports) yang dapat dihasilkan, diunduh sebagai PDF, dan diarsipkan.

> **Akses:**
>
> - `admin` & `supervisor`: melihat dashboard, membuat laporan bulanan, mengunduh PDF, dan menghapus laporan.
> - `petugas` & `tenant`: melihat dashboard (statistik terbatas sesuai peran).
>
> **Rute:**
>
> - `GET /dashboard` → `DashboardController@index` (nama rute: `dashboard`)
> - `GET /reports` → `UsageReportController@index` (nama rute: `reports.index`)
> - `POST /reports/generate` → `UsageReportController@generate` (nama rute: `reports.generate`)
> - `GET /reports/{id}/pdf` → `UsageReportController@exportPdf` (nama rute: `reports.pdf`)
> - `DELETE /reports/{id}` → `UsageReportController@destroy` (nama rute: `reports.destroy`)

## 1. Dashboard

Halaman `GET /dashboard` menampilkan ringkasan operasional dalam bentuk kartu statistik dan grafik. Data dihitung secara real-time dari seluruh data sistem.

### 1.1 Kartu Statistik

| Statistik | Sumber Data | Keterangan |
| --- | --- | --- |
| Jumlah Tenant | `Tenant::count()` | Total unit/penyewa terdaftar |
| Jumlah Invoice Bulan Ini | `Invoice::whereMonth`/`whereYear` | Invoice yang diterbitkan pada bulan berjalan |
| Total Nilai Invoice | `Invoice::sum('total_amount')` | Akumulasi nilai seluruh invoice |
| Invoice Lunas | `Invoice::where('status','paid')->count()` | Jumlah invoice berstatus `paid` |
| Invoice Tertunggak | `Invoice::where('status','unpaid')->count()` | Jumlah invoice berstatus `unpaid` |
| Progress Baca Meter | `MeterReading` bulan berjalan | Persentase unit yang sudah dibaca meter-nya |

### 1.2 Grafik dan Visualisasi

Dashboard menampilkan grafik pendukung untuk memantau tren:

- **Grafik Pemakaian Listrik & Air** — tren pemakaian per bulan berdasarkan data meter reading.
- **Grafik Pendapatan** — tren total tagihan vs total pembayaran per bulan.
- **Status Pembayaran** — distribusi invoice berdasarkan status (`paid`, `unpaid`, `overdue`).

> Catatan: Data grafik dihitung dari query agregasi pada model `Invoice`, `Payment`, dan `MeterReading`. Pastikan data operasional terisi agar grafik akurat.

## 2. Laporan Pemakaian Bulanan (Usage Reports)

Laporan pemakaian bulanan merangkum total unit yang ditagih, total pemakaian listrik/air/lainnya, serta total pendapatan yang diharapkan untuk satu bulan tertentu. Laporan disimpan di database dan dapat diunduh sebagai PDF.

### 2.1 Melihat Daftar Laporan

Halaman `GET /reports` menampilkan daftar laporan yang sudah dibuat dalam tabel paginasi (10 entri per halaman). Fitur pencarian:

- **Pencarian berdasarkan bulan** (`?month=`): filter laporan berdasarkan `month_year` (format `YYYY-MM`). Jika parameter `month` diberikan, hanya laporan untuk bulan tersebut yang ditampilkan.
- Parameter filter dipertahankan pada link paginasi.

Kolom yang ditampilkan:

| Kolom | Keterangan |
| --- | --- |
| Bulan (`month_year`) | Periode laporan, format `YYYY-MM` |
| Total Unit Ditagih | Jumlah invoice pada bulan tersebut |
| Total Pemakaian Listrik | Akumulasi kWh listrik |
| Total Pemakaian Air | Akumulasi m³ air |
| Total Lainnya | Pemakaian kategori lain |
| Total Pendapatan Diharapkan | Akumulasi nilai invoice |
| Aksi | Tombol Unduh PDF, Hapus |

### 2.2 Membuat Laporan Baru

Form pembuatan laporan diakses dari halaman daftar laporan. Langkah:

1. Pilih bulan (`month_year`) yang ingin dilaporkan (format `YYYY-MM`).
2. Klik tombol **Generate** untuk memicu `POST /reports/generate`.

Proses generate (`UsageReportController@generate`):

1. Sistem memanggil `UsageReport::calculateMonthlyStats($month)` yang menghitung:
   - `total_units_billed` — jumlah invoice pada bulan tersebut.
   - `total_electric_usage` — total pemakaian listrik dari meter reading terkait.
   - `total_water_usage` — total pemakaian air.
   - `total_others` — total pemakaian kategori lain.
   - `total_revenue_expected` — akumulasi `total_amount` dari invoice bulan tersebut.
2. Record `UsageReport` disimpan ke database dengan field `month_year` dan statistik di atas.
3. Pengguna diarahkan kembali ke daftar laporan dengan pesan sukses.

> **Catatan:** Jika laporan untuk bulan yang sama sudah ada, sistem tetap membuat record baru. Hindari duplikasi dengan memeriksa daftar laporan sebelum generate.

### 2.3 Mengunduh Laporan sebagai PDF

Tombol **PDF** pada baris laporan memicu `GET /reports/{id}/pdf`:

1. Sistem mengambil record `UsageReport` berdasarkan ID.
2. Data diteruskan ke view PDF (DomPDF/barryvdh).
3. Browser mengunduh/menampilkan file PDF berisi ringkasan statistik bulanan.

Format PDF mencakup:

- Header aplikasi dan judul laporan
- Periode (`month_year`)
- Tabel ringkasan: total unit, pemakaian listrik/air/lainnya, pendapatan diharapkan
- Tanggal cetak

### 2.4 Menghapus Laporan

Tombol **Hapus** pada baris laporan memicu `DELETE /reports/{id}`:

1. Sistem mengambil record `UsageReport` berdasarkan ID.
2. Record dihapus dari database.
3. Pengguna diarahkan kembali ke daftar laporan dengan pesan sukses.

> **Peringatan:** Penghapusan laporan bersifat permanen. Data statistik tetap dapat diregenerasi kapan saja melalui tombol **Generate** selama data invoice dan meter reading bulan terkait masih ada.

## 3. Alur Kerja yang Disarankan

1. **Akhir bulan** — Pastikan seluruh meter reading selesai dan invoice sudah diterbitkan untuk bulan tersebut.
2. **Generate laporan** — Buka `GET /reports`, pilih bulan, lalu klik **Generate**.
3. **Verifikasi** — Periksa nilai pada daftar laporan; bandingkan dengan dashboard untuk konsistensi.
4. **Unduh PDF** — Simpan arsip PDF untuk keperluan audit/laporan manajemen.
5. **Arsip** — Hanya hapus laporan jika sudah tidak relevan (mis. data sumber sudah dirotasi).

## 4. Tips dan Pemecahan Masalah

| Masalah | Kemungkinan Penyebab | Tindakan |
| --- | --- | --- |
| Laporan kosong (semua nilai 0) | Belum ada invoice/meter reading untuk bulan tersebut | Pastikan data operasional bulan itu sudah lengkap, lalu generate ulang |
| Grafik dashboard tidak muncul | Data invoice/payment/meter reading kosong | Isi data operasional terlebih dahulu |
| PDF gagal diunduh | Ekstensi PDF belum terinstal atau izin storage | Periksa konfigurasi DomPDF dan izin folder `storage/` |
| Duplikat laporan bulan sama | Generate berulang untuk bulan yang sama | Hapus duplikat, gunakan satu record per bulan |
| Pencarian `?month=` tidak menemukan | Format salah | Gunakan format `YYYY-MM` (contoh: `2025-03`) |
