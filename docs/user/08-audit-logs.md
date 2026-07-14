# 08. Log Audit

Modul **Log Audit** mencatat seluruh aktivitas data yang terjadi di sistem Braga8 — mulai dari penambahan, pembaruan, hingga penghapusan data. Catatan ini membantu admin dan manajer memantau siapa melakukan apa, kapan, dan terhadap data mana, sehingga setiap perubahan dapat ditelusuri kembali secara akuntabel.

---

## 08.1 Peran yang Dapat Mengakses

| Peran | Akses |
| ------- | ------- |
| Admin | Penuh — melihat, mencari, memfilter, dan mengarsipkan log |
| Manajer | Penuh — melihat, mencari, dan memfilter log |
| STAF | Tidak dapat mengakses halaman Log Audit |

> Log Audit hanya tersedia untuk peran **Admin** dan **Manajer**. Staf operasional tidak memiliki menu ini.

---

## 08.2 Membuka Halaman Log Audit

1. Login sebagai Admin atau Manajer.
2. Pada sidebar, klik menu **Log Audit**.
3. Anda akan diarahkan ke halaman daftar riwayat aktivitas sistem.

Halaman menampilkan tabel **Riwayat Aktivitas Sistem** dengan tiga kolom utama:

| Kolom | Keterangan |
| ------- | ------------ |
| **Pengguna** | Nama pengguna yang melakukan aksi. Jika aksi dilakukan otomatis oleh sistem, ditampilkan sebagai "Sistem". |
| **Detail Aktivitas** | Deskripsi aksi dalam Bahasa Indonesia, misalnya: *"Menambahkan tagihan baru: **INV-2026-001**"*. |
| **Waktu Kejadian** | Tanggal (`d M Y`) dan jam (`H:i`) saat aksi terjadi. |

---

## 08.3 Apa yang Dicatat?

Log Audit secara otomatis mencatat tiga jenis aksi pada model berikut:

| Jenis Aksi | Label di UI | Kapan dicatat |
| ------------ | ------------- | --------------- |
| `created` | **Dibuat** | Saat data baru ditambahkan |
| `updated` | **Diperbarui** | Saat data yang ada diubah |
| `deleted` | **Dihapus** | Saat data dihapus dari sistem |

### Model yang Dipantau

Trait `LogsActivity` terpasang pada model berikut, sehingga setiap create/update/delete pada model-model ini otomatis menulis satu baris log:

- **Pengguna** (`users`)
- **Pelanggan** (`tenants`)
- **Unit** (`units`)
- **Meteran** (`utility_meters`)
- **Pencatatan Meteran** (`meter_readings`) — dengan deskripsi khusus yang menyebut jenis meter (listrik/air), nomor unit, nama tenant, dan nilai bacaan beserta satuan (kWh atau m³)
- **Tarif** (`tariffs`)
- **Tagihan** (`invoices`)
- **Detail Tagihan** (`invoice_items`)
- **Pembayaran** (`payments`)
- **Komplain** (`complaints`)
- **Pengingat** (`reminders`)
- **Laporan Penggunaan** (`usage_reports`)
- **Notifikasi** (`notifications`)

### Contoh Deskripsi Aktivitas

- *"Andi menambahkan pelanggan baru 'PT Sumber Makmur'"*
- *"Siti memperbarui informasi tagihan 'INV-2026-001'"*
- *"Budi menghapus pembayaran 'PAY-2026-045'"*
- *"Rina menginput bacaan meter listrik Unit A-12 (Toko Berkah) — 1250 kWh"*

---

## 08.4 Mencari Aktivitas

Kotak pencarian di pojok kiri atas memungkinkan Anda mencari log berdasarkan kata kunci.

### Yang Bisa Dicari

Pencarian mencocokkan kata kunci (case-insensitive) terhadap:

1. **Jenis aksi** — mendukung kata kunci Bahasa Indonesia:
   - `tambah`, `menambah`, `buat` → mencocokkan aksi *Dibuat*
   - `ubah`, `edit`, `perbarui` → mencocokkan aksi *Diperbarui*
   - `hapus` → mencocokkan aksi *Dihapus*
2. **Nama tabel** — misalnya `invoices`, `payments`, `tenants`
3. **ID record** — misalnya `42`
4. **Nama pengguna** — misalnya `Andi`

### Cara Menggunakan

1. Ketik kata kunci pada kotak **"Cari aktivitas.."**.
2. Tekan **Enter** atau klik ikon kaca pembesar.
3. Hasil yang cocok akan ditampilkan di tabel.

> Kombinasikan pencarian dengan filter (lihat 08.5) untuk hasil yang lebih spesifik.

---

## 08.5 Memfilter Aktivitas

Klik tombol **slider/filter** (ikon tiga garis horizontal) di sebelah kotak pencarian untuk membuka panel filter. Panel ini memiliki dua dropdown:

### Filter 1 — Jenis Aktivitas

| Pilihan | Nilai | Arti |
| --------- | ------- | ------ |
| Semua Aktivitas | *(kosong)* | Tidak ada filter jenis aksi |
| Dibuat | `created` | Hanya aksi penambahan data |
| Diperbarui | `updated` | Hanya aksi perubahan data |
| Dihapus | `deleted` | Hanya aksi penghapusan data |

### Filter 2 — Kategori Data

Dropdown ini menampilkan daftar tabel yang ada di log (misalnya `Invoices`, `Payments`, `Tenants`, `Meter readings`, dst.). Daftar ini diambil secara dinamis dari log yang belum diarsipkan, sehingga hanya kategori yang benar-benar memiliki entri yang muncul.

| Pilihan | Arti |
| --------- | ------ |
| Semua Kategori | Tidak ada filter kategori |
| *`<nama tabel>`* | Hanya log untuk tabel tersebut |

### Menerapkan & Mereset Filter

- **Terapkan** — klik tombol coklat di kanan bawah panel untuk menerapkan filter.
- **Reset** — klik tombol abu-abu di kiri bawah panel, atau klik menu **Log Audit** di sidebar untuk menghapus semua filter dan pencarian.

> Saat ada filter aktif, tombol filter akan menampilkan **ring amber** sebagai indikator visual.

---

## 08.6 Pengarsipan Otomatis

Untuk menjaga halaman Log Audit tetap ringan dan responsif, sistem menerapkan **pengarsipan otomatis**:

- Setiap kali halaman Log Audit dibuka, sistem mengambil **50 log terbaru**.
- Log di luar 50 terbaru secara otomatis ditandai sebagai `is_archived = true`.
- Log yang diarsipkan **tidak ditampilkan** di halaman utama, namun **tetap tersimpan di database** untuk keperluan audit forensik.

> Pengarsipan bersifat *soft archive* — data tidak dihapus, hanya disembunyikan dari tampilan default. Tim teknis masih dapat mengaksesnya melalui query database langsung jika diperlukan untuk investigasi.

---

## 08.7 Paginasi

- Tabel menampilkan **10 entri per halaman**.
- Navigasi paginasi berada di bawah tabel.
- Info di kiri bawah menampilkan rentang entri yang sedang ditampilkan, misalnya: *"Menampilkan 1 sampai 10 dari 27 hasil"*.
- Filter dan pencarian **dipertahankan** saat berpindah halaman (query string dipertahankan).

---

## 08.8 API Log Audit per Pengguna

Selain halaman web, tersedia endpoint API untuk mengambil log aktivitas pengguna yang sedang login:

| Metode | Endpoint | Keterangan |
| -------- | ---------- | ------------ |
| `GET` | `/api/audit-logs` | Mengembalikan log aktivitas pengguna terotentikasi (paginasi 10 entri, hanya log yang belum diarsipkan) |

### Format Respons

```json
{
  "data": [
    {
      "id": 12,
      "user_id": 3,
      "action": "created",
      "table_name": "invoices",
      "record_id": 45,
      "description": "Andi menambahkan tagihan baru 'INV-2026-001'",
      "is_archived": false,
      "created_at": "2026-04-10T08:30:00.000000Z",
      "updated_at": "2026-04-10T08:30:00.000000Z",
      "user": {
        "id": 3,
        "name": "Andi"
      }
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "total": 27 }
}
```

> Endpoint ini berguna untuk integrasi dashboard eksternal atau notifikasi real-time berbasis aktivitas pengguna.

---

## 08.9 Tips Penggunaan

1. **Telusuri perubahan tagihan** — filter Kategori = `Invoices` untuk melihat siapa membuat/mengubah/menghapus tagihan.
2. **Audit penghapusan data** — filter Jenis Aktivitas = `Dihapus` untuk memantau semua aksi penghapusan, yang sering kali sensitif.
3. **Periksa aktivitas pengguna tertentu** — gunakan kotak pencarian dengan nama pengguna.
4. **Pantau input meter** — filter Kategori = `Meter readings` untuk memverifikasi siapa mencatat bacaan meter dan kapan.
5. **Investigasi anomali** — jika data berubah tanpa sebab jelas, periksa log dengan Kategori tabel terkait dan rentang waktu kejadian.

---

## 08.10 Batasan & Catatan

- Log Audit **tidak dapat diedit atau dihapus** melalui UI — ini menjaga integritas rantai audit.
- Hanya 50 log terbaru yang terlihat di UI; log lama tetap tersimpan di database (status `is_archived`).
- Aksi yang dilakukan sebelum fitur Log Audit diaktifkan tidak memiliki catatan retroaktif.
- Pencarian tidak mendukung pencarian rentang tanggal — gunakan urutan kronologis terbalik (terbaru di atas) untuk navigasi temporal.
