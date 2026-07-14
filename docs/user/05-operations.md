# Operations

Modul Operations merupakan inti aktivitas harian sistem Braga8 Utility Billing. Modul ini mencakup pembacaan meter, penerbitan tagihan, penerimaan pembayaran, dan pengiriman pengingat. Seluruh aktivitas operasional mengalir dari data master yang telah dikelola (lihat [04-master-data.md](04-master-data.md)).

```text
MeterReading ─→ Invoice ─→ Payment
     ↑              ↓
UtilityMeter   Reminder
```

> **Akses:** Modul operasi melibatkan seluruh peran:
>
> - `admin` & `supervisor`: akses penuh ke pembacaan meter, tagihan, pembayaran, dan pengingat.
> - `petugas`: membuat dan memperbarui pembacaan meter; melihat tagihan dan pembayaran.
> - `tenant`: melihat tagihan dan pembayaran miliknya, serta membuat pembayaran.
>
> **Catatan Keamanan:** Beberapa endpoint menerapkan pembatasan peran secara longgar. Lihat `SECURITY_AUDIT_REPORT.md` dan [02-roles-and-access.md](02-roles-and-access.md) untuk detail celah BOLA. Panduan ini mendokumentasikan perilaku yang dimaksud (intended behaviour).

## 1. Pembacaan Meter (Meter Readings)

Modul Meter Readings mencatat konsumsi utilitas (listrik, air, gas) per unit per periode. Data pembacaan menjadi dasar perhitungan tagihan.

> **Rute:** `Route::resource('meter-readings', MeterReadingController::class)` → `GET /meter-readings`, `GET /meter-readings/create`, `POST /meter-readings`, `GET /meter-readings/{reading}/edit`, `PUT /meter-readings/{reading}`, `DELETE /meter-readings/{reading}`.
> Rute tambahan: `GET /meter-readings/summary`, `GET /meter-readings/monthly-progress`, `PATCH /meter-readings/{reading}/status`.

### 1.1 Daftar Pembacaan Meter

Halaman `GET /meter-readings` menampilkan daftar pembacaan meter dalam tabel paginasi (10 entri per halaman). Fitur pencarian tersedia melalui parameter `search` yang mencari pada:

- Nama tenant (penyewa unit terkait meter)
- Nomor unit

Halaman juga menampilkan daftar utility meters yang tersedia untuk memudahkan pemilihan meter saat input pembacaan baru.

### 1.2 Menambah Pembacaan Meter

Form tambah pembacaan (`GET /meter-readings/create`) memerlukan isian berikut:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Unit (`unit_id`) | Ya | `required`, exists di `units` | Unit yang meter-nya dibaca |
| Jenis Meter (`meter_type`) | Ya | `required`, `in:electricity,water,gas` | Jenis utilitas |
| Meter (`meter_id`) | Ya | `required`, exists di `utility_meters` | Meter yang dibaca |
| Nilai Pembacaan (`reading_value`) | Ya | `required`, numeric, ≥ pembacaan terakhir | Angka konsumsi meter |
| Foto (`photo`) | Tidak | file gambar ATAU string base64 | Bukti foto meter |
| Latitude | Tidak | numeric | Koordinat lokasi pembacaan |
| Longitude | Tidak | numeric | Koordinat lokasi pembacaan |
| Keterangan (`description`) | Tidak | string | Catatan tambahan |

**Aturan penting:**

- `reading_value` **tidak boleh lebih kecil** dari pembacaan terakhir yang tercatat untuk meter yang sama. Pelanggaran aturan ini memicu error validasi.
- Foto dapat diunggah sebagai file (upload biasa) atau sebagai string base64 (mis. dari kamera perangkat mobile). Sistem otomatis mendeteksi format dan menyimpan foto ke storage.
- Bila `latitude` dan `longitude` diisi, sistem secara opsional akan mengambil alamat lokasi (reverse geocoding) melalui layanan **Nominatim OpenStreetMap** dan menyimpannya bersama data pembacaan. Kegagalan API Nominatim dicatat ke log namun tidak membatalkan penyimpanan.

Setelah submit (`POST /meter-readings`), pembacaan disimpan dan pengguna diarahkan kembali ke daftar dengan status `meter-reading-created`.

### 1.3 Mengubah Pembacaan Meter

Form ubah pembacaan (`GET /meter-readings/{reading}/edit`) memungkinkan memperbarui data pembacaan. Aturan validasi sama dengan penambahan, termasuk pemeriksaan `reading_value` terhadap pembacaan terakhir. Foto baru (jika diunggah) akan menggantikan foto lama.

Submit melalui `PUT /meter-readings/{reading}` mengembalikan status `meter-reading-updated`.

### 1.4 Memperbarui Status Pembacaan

Endpoint `PATCH /meter-readings/{reading}/status` mengubah status pembacaan meter:

- Jika status saat ini `null` → diubah menjadi `checked`
- Jika status saat ini `checked` → diubah kembali menjadi `null`

Status `checked` menandai pembacaan telah diverifikasi/validasi oleh supervisor atau admin.

### 1.5 Ringkasan Pembacaan (Summary)

Endpoint `GET /meter-readings/summary` mengembalikan respons JSON berisi daftar tenant beserta:

- Unit-unit milik tenant
- Meter-meter di setiap unit
- Pembacaan terakhir untuk setiap meter

Endpoint ini digunakan oleh komponen UI (mis. dashboard atau widget ringkasan) untuk menampilkan status pembacaan per tenant.

### 1.6 Progres Bulanan (Monthly Progress)

Endpoint `GET /meter-readings/monthly-progress` mengembalikan respons JSON:

| Field | Tipe | Keterangan |
| --- | --- | --- |
| `total_meters` | int | Total seluruh utility meter |
| `meters_read_this_month` | int | Jumlah meter yang sudah dibaca bulan ini |
| `progress_percentage` | float | Persentase meter yang sudah dibaca (0–100) |

Endpoint ini mendukung widget progres pembacaan di dashboard.

### 1.7 Menghapus Pembacaan

Tombol hapus (`DELETE /meter-readings/{reading}`) menghapus entri pembacaan meter. Status yang ditampilkan: `meter-reading-deleted`.

> ⚠️ **Perhatian:** Menghapus pembacaan yang sudah menjadi dasar tagihan dapat menyebabkan inkonsistensi data tagihan. Verifikasi dampak sebelum menghapus.

## 2. Tagihan (Invoices)

Modul Invoices menerbitkan tagihan utilitas kepada tenant berdasarkan pembacaan meter dan tarif yang berlaku.

> **Rute:** `Route::resource('invoices', InvoiceController::class)` → `GET /invoices`, `GET /invoices/create`, `POST /invoices`, `GET /invoices/{invoice}`, `GET /invoices/{invoice}/edit`, `PUT /invoices/{invoice}`, `DELETE /invoices/{invoice}`.
> Rute tambahan: `GET /invoices/{invoice}/pdf`.

### 2.1 Daftar Tagihan

Halaman `GET /invoices` menampilkan daftar tagihan dalam tabel paginasi. Fitur pencarian dan filter tersedia:

- Pencarian berdasarkan nomor tagihan atau nama tenant
- Filter berdasarkan status tagihan (mis. unpaid, paid, overdue)

### 2.2 Menerbitkan Tagihan

Penerbitan tagihan (`POST /invoices`) menghitung jumlah tagihan berdasarkan:

1. Pembacaan meter terbaru untuk unit & jenis utilitas terpilih
2. Selisih dengan pembacaan sebelumnya (konsumsi periode berjalan)
3. Tarif yang berlaku untuk jenis utilitas tersebut
4. Biaya tambahan/diskon jika ada

Field yang divalidasi saat menerbitkan tagihan mencakup unit, jenis utilitas, periode, dan meter yang menjadi dasar perhitungan. Detail field dan aturan validasi mengikuti implementasi `InvoiceController::store`.

Setelah submit, tagihan disimpan dengan status awal `unpaid` dan pengguna diarahkan kembali ke daftar dengan status `invoice-created`.

### 2.3 Melihat Detail Tagihan

Halaman `GET /invoices/{invoice}` menampilkan rincian tagihan:

- Informasi tenant dan unit
- Jenis utilitas dan periode tagihan
- Pembacaan awal, pembacaan akhir, dan selisih konsumsi
- Tarif yang diterapkan
- Subtotal, pajak/biaya tambahan, dan total tagihan
- Status pembayaran

### 2.4 Mengubah Tagihan

Form ubah tagihan (`GET /invoices/{invoice}/edit`) memungkinkan memperbarui data tagihan. Submit melalui `PUT /invoices/{invoice}` mengembalikan status `invoice-updated`.

> ⚠️ **Perhatian:** Mengubah tagihan yang sudah dibayar dapat menyebabkan inkonsistensi laporan keuangan. Lakukan perubahan hanya pada tagihan yang belum dibayar.

### 2.5 Mengunduh PDF Tagihan

Endpoint `GET /invoices/{invoice}/pdf` menghasilkan dokumen PDF tagihan untuk dicetak atau dikirim kepada tenant. PDF berisi seluruh rincian tagihan dan dapat digunakan sebagai bukti tagihan resmi.

### 2.6 Menghapus Tagihan

Tombol hapus (`DELETE /invoices/{invoice}`) menghapus tagihan. Status yang ditampilkan: `invoice-deleted`.

> ⚠️ **Perhatian:** Menghapus tagihan yang sudah dibayar tidak disarankan. Pertimbangkan untuk membatalkan (void) alih-alih menghapus agar jejak audit tetap utuh.

## 3. Pembayaran (Payments)

Modul Payments mencatat penerimaan pembayaran tagihan dari tenant. Pembayaran dapat dilakukan oleh admin, supervisor, atau tenant sendiri.

> **Rute:** `Route::resource('payments', PaymentController::class)` → `GET /payments`, `GET /payments/create`, `POST /payments`, `GET /payments/{payment}`, `GET /payments/{payment}/edit`, `PUT /payments/{payment}`, `DELETE /payments/{payment}`.

### 3.1 Daftar Pembayaran

Halaman `GET /payments` menampilkan daftar pembayaran dalam tabel paginasi. Fitur pencarian dan filter:

- Pencarian berdasarkan nomor tagihan atau nama tenant
- Filter berdasarkan metode pembayaran dan status

### 3.2 Mencatat Pembayaran

Form catat pembayaran (`GET /payments/create`) memerlukan isian:

| Field | Wajib | Keterangan |
| --- | --- | --- |
| Tagihan (`invoice_id`) | Ya | Tagihan yang dibayar |
| Jumlah (`amount`) | Ya | Nominal pembayaran |
| Metode (`payment_method`) | Ya | cash, transfer, e-wallet, dll. |
| Tanggal Pembayaran (`payment_date`) | Ya | Tanggal penerimaan |
| Referensi (`reference_number`) | Tidak | Nomor referensi/bukti transfer |
| Keterangan (`notes`) | Tidak | Catatan tambahan |

Setelah submit (`POST /payments`):

1. Pembayaran disimpan dan dikaitkan dengan tagihan terkait.
2. Status tagihan diperbarui — bila jumlah dibayar mencukupi total tagihan, status tagihan berubah menjadi `paid`.
3. Pengguna diarahkan kembali ke daftar dengan status `payment-created`.

### 3.3 Melihat Detail Pembayaran

Halaman `GET /payments/{payment}` menampilkan rincian pembayaran: tagihan terkait, jumlah, metode, tanggal, referensi, dan keterangan.

### 3.4 Mengubah Pembayaran

Form ubah pembayaran (`GET /payments/{payment}/edit`) memungkinkan memperbarui data pembayaran. Perubahan jumlah pembayaran dapat memengaruhi status tagihan (mis. dari `paid` kembali ke `unpaid` jika jumlah dikurangi). Submit melalui `PUT /payments/{payment}` mengembalikan status `payment-updated`.

### 3.5 Menghapus Pembayaran

Tombol hapus (`DELETE /payments/{payment}`) menghapus catatan pembayaran. Status tagihan terkait akan dihitung ulang. Status yang ditampilkan: `payment-deleted`.

> ⚠️ **Perhatian:** Menghapus pembayaran yang sudah rekonsiliasi dengan laporan keuangan dapat menyebabkan inkonsistensi. Pertimbangkan untuk mencatat koreksi alih-alih menghapus.

## 4. Pengingat (Reminders)

Modul Reminders mengirim pengingat tagihan kepada tenant yang belum membayar. Pengingat dapat dipicu manual atau melalui scheduler.

> **Rute:** `Route::resource('reminders', ReminderController::class)` → `GET /reminders`, `GET /reminders/create`, `POST /reminders`, `GET /reminders/{reminder}`, `GET /reminders/{reminder}/edit`, `PUT /reminders/{reminder}`, `DELETE /reminders/{reminder}`.
> Scheduler: `php artisan reminders:send` (lihat [03-commands-and-scheduling.md](../developer/03-commands-and-scheduling.md)).

### 4.1 Daftar Pengingat

Halaman `GET /reminders` menampilkan daftar pengingat yang telah dikirim atau dijadwalkan, beserta status pengiriman.

### 4.2 Mengirim Pengingat Manual

Form kirim pengingat (`GET /reminders/create`) memerlukan:

| Field | Wajib | Keterangan |
| --- | --- | --- |
| Tagihan (`invoice_id`) | Ya | Tagihan yang diingatkan |
| Tenant | Otomatis | Diambil dari tagihan |
| Saluran (`channel`) | Ya | email / sms / whatsapp |
| Pesan (`message`) | Tidak | Pesan kustom; bila kosong, template default digunakan |

Setelah submit (`POST /reminders`), pengingat dibuat dan (bila saluran didukung) langsung dikirim. Status `reminder-created` ditampilkan.

### 4.3 Pengiriman Otomatis (Scheduler)

Command artisan `reminders:send` berjalan terjadwal (lihat `app/Console/Kernel.php`) untuk mengirim pengingat otomatis kepada tenant dengan tagihan jatuh tempo. Logika pengiriman:

1. Ambil tagihan berstatus `unpaid` yang telah melewati jatuh tempo.
2. Buat entri reminder per tagihan.
3. Kirim notifikasi melalui saluran yang dikonfigurasi (email/SMS/WA).
4. Catat status pengiriman (sent / failed).

> 📌 **Catatan:** Konfigurasi saluran notifikasi (mail driver, SMS gateway, WA API) berada di file `.env` dan `config/services.php`. Pastikan kredensial sudah diatur sebelum mengaktifkan scheduler.

### 4.4 Melihat & Mengubah Pengingat

- `GET /reminders/{reminder}`: menampilkan detail pengingat dan status pengiriman.
- `GET /reminders/{reminder}/edit` + `PUT /reminders/{reminder}`: memperbarui pengingat yang belum terkirim. Status: `reminder-updated`.

### 4.5 Menghapus Pengingat

Tombol hapus (`DELETE /reminders/{reminder}`) menghapus catatan pengingat. Status: `reminder-deleted`.

## 5. Alur Kerja Operasional

Alur lengkap operasi harian Braga8 Utility Billing:

```text
1. Petugas membaca meter        → MeterReading (create)
2. Supervisor memverifikasi     → MeterReading (status: checked)
3. Admin/supervisor terbitkan   → Invoice (create)
4. Sistem kirim pengingat       → Reminder (auto/manual)
5. Tenant/admin bayar           → Payment (create)
6. Status tagihan: paid         → Invoice status diperbarui
7. Cetak PDF tagihan/bukti      → Invoice PDF / Payment record
```

### Tips Operasional

- **Urutan input:** Pastikan master data (unit, meter, tariff) lengkap sebelum input pembacaan meter. Pembacaan meter wajib memiliki meter & unit yang valid.
- **Verifikasi pembacaan:** Gunakan status `checked` untuk menandai pembacaan yang sudah diverifikasi sebelum tagihan diterbitkan.
- **Foto bukti:** Selalu unggah foto meter sebagai bukti pembacaan untuk menghindari sengketa konsumsi.
- **Lokasi GPS:** Aktifkan GPS saat input pembacaan dari lapangan; koordinat tercatat sebagai bukti kunjungan.
- **Pengingat otomatis:** Aktifkan scheduler `reminders:send` di cron server untuk pengingat konsisten.
- **Rekonsiliasi:** Sebelum menutup periode, pastikan seluruh pembayaran tercatat dan status tagihan sudah `paid`.

## 6. Hak Akses per Operasi

| Operasi | admin | supervisor | petugas | tenant |
| --- | :---: | :---: | :---: | :---: |
| Lihat pembacaan meter | ✅ | ✅ | ✅ | ❌ |
| Input pembacaan meter | ✅ | ✅ | ✅ | ❌ |
| Verifikasi pembacaan (status) | ✅ | ✅ | ❌ | ❌ |
| Lihat tagihan | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Terbitkan tagihan | ✅ | ✅ | ❌ | ❌ |
| Unduh PDF tagihan | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Lihat pembayaran | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Catat pembayaran | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Kelola pengingat | ✅ | ✅ | ❌ | ❌ |
| Lihat pengingat | ✅ | ✅ | ✅ | ❌ |

> ✅ = diizinkan, ❌ = tidak diizinkan. Untuk tenant, akses terbatas pada data miliknya sendiri.

## 7. Referensi Terkait

- [04-master-data.md](04-master-data.md) — Master data yang menjadi dasar operasi
- [03-dashboard.md](03-dashboard.md) — Widget progres pembacaan & ringkasan tagihan
- [06-complaints.md](06-complaints.md) — Komplain terkait tagihan/pembayaran
- [07-reports.md](07-reports.md) — Laporan operasional
- [../developer/03-commands-and-scheduling.md](../developer/03-commands-and-scheduling.md) — Scheduler pengingat otomatis
