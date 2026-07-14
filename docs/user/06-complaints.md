# Komplain (Complaints)

Modul Komplain memungkinkan tenant dan pengguna lain melaporkan kendala terkait tagihan, pembayaran, kualitas utilitas, atau masalah operasional lainnya. Admin/supervisor menindaklanjuti komplain dengan memberikan solusi dan memperbarui status.

> **Akses:**
>
> - `admin` & `supervisor`: melihat, memperbarui status, menanggapi, dan menghapus komplain.
> - `petugas`: membuat komplain.
> - `tenant`: membuat komplain dan melihat komplain miliknya.
>
> **Rute:** `Route::resource('complaints', ComplaintController::class)` → `GET /complaints`, `GET /complaints/create`, `POST /complaints`, `GET /complaints/{complaint}`, `GET /complaints/{complaint}/edit`, `PUT /complaints/{complaint}`, `DELETE /complaints/{complaint}`.
> Rute tambahan: `POST /complaints/{complaint}/action` (aksi penyelesaian).

## 1. Daftar Komplain

Halaman `GET /complaints` menampilkan daftar komplain dalam tabel paginasi (10 entri per halaman). Fitur pencarian dan pengurutan:

- **Pencarian** (`?search=`): mencari pada field `reported_by` (pelapor) dan `description` (deskripsi komplain).
- **Pengurutan** (`?sort=`):
  - `latest` (default) — komplain terbaru di atas.
  - `oldest` — komplain terlama di atas.

Parameter filter dipertahankan pada link paginasi.

## 2. Membuat Komplain

Form buat komplain (`GET /complaints/create`) memerlukan isian:

| Field | Wajib | Aturan Validasi | Keterangan |
| --- | --- | --- | --- |
| Judul (`title`) | Ya | `required`, string, max 255 | Ringkasan masalah |
| Deskripsi (`description`) | Ya | `required`, string | Penjelasan detail masalah |
| Tanggal Lapor (`report_date`) | Ya | `required`, date | Tanggal kejadian/pelaporan |
| Gambar (`image`) | Tidak | image, max 2 MB | Bukti foto (opsional) |

Setelah submit (`POST /complaints`):

1. Bila ada gambar, file disimpan ke storage disk `public` di folder `complaints`.
2. Komplain disimpan dengan status awal `pending`.
3. Pengguna diarahkan ke daftar komplain dengan pesan sukses.

## 3. Melihat Detail Komplain

Halaman `GET /complaints/{complaint}` menampilkan rincian komplain:

- Judul, deskripsi, tanggal lapor
- Pelapor (`reported_by`)
- Status (`pending`, `in_progress`, `resolved`, `rejected`)
- Solusi (jika sudah ditanggapi)
- Gambar bukti (jika ada)

## 4. Mengubah Komplain

Form ubah komplain (`GET /complaints/{complaint}/edit`) memungkinkan memperbarui:

| Field | Aturan Validasi |
| --- | --- |
| Judul (`title`) | `sometimes`, string, max 255 |
| Deskripsi (`description`) | `sometimes`, string |
| Status (`status`) | `sometimes`, `in:pending,in_progress,resolved,rejected` |
| Solusi (`solution`) | nullable, string |
| Gambar (`image`) | nullable, image, max 2 MB |

Bila gambar baru diunggah, gambar lama dihapus dari storage untuk menghemat ruang. Submit melalui `PUT /complaints/{complaint}` mengembalikan pengguna ke halaman detail dengan pesan sukses.

### Status Komplain

| Status | Arti |
| --- | --- |
| `pending` | Komplain baru diajukan, belum ditindaklanjuti |
| `in_progress` | Komplain sedang ditangani |
| `resolved` | Komplain telah diselesaikan dengan solusi |
| `rejected` | Komplain ditolak (tidak valid/di luar scope) |

## 5. Aksi Penyelesaian (Action)

Endpoint `POST /complaints/{complaint}/action` adalah jalur cepat untuk menyelesaikan komplain:

1. Validasi: field `solution` wajib diisi, minimal 5 karakter.
2. Komplain diperbarui: `solution` diisi dan `status` diubah menjadi `resolved`.
3. Bila komplain terkait user (`user_id` terisi), notifikasi otomatis dibuat:
   - **Judul:** "Komplain Ditanggapi"
   - **Pesan:** "Komplain Anda *[judul]* telah mendapat solusi dari admin."
   - **Tipe:** `complaint`
4. Pengguna diarahkan kembali dengan status `complaint-resolved`.

> 📌 **Catatan:** Notifikasi muncul di panel notifikasi user pelapor (lihat [09-profile-notifications.md](09-profile-notifications.md)).

## 6. Menghapus Komplain

Tombol hapus (`DELETE /complaints/{complaint}`):

1. Bila komplain memiliki gambar, file gambar dihapus dari storage.
2. Catatan komplain dihapus dari database.
3. Status `complaint-deleted` ditampilkan.

> ⚠️ **Perhatian:** Menghapus komplain menghilangkan jejak audit. Pertimbangkan untuk mengubah status menjadi `rejected` alih-alih menghapus jika komplain perlu tetap tercatat.

## 7. Alur Kerja Komplain

```text
1. Tenant/petugas buat komplain    → Complaint (status: pending)
2. Admin/supervisor review         → Complaint (status: in_progress)
3. Admin berikan solusi            → action endpoint
   - status: resolved
   - solusi tercatat
   - notifikasi ke pelapor
4. (Alternatif) komplain ditolak   → Complaint (status: rejected)
```

### Tips Pengelolaan Komplain

- **Respons cepat:** Komplain berstatus `pending` lebih dari 24 jam sebaiknya segera diproses untuk menjaga kepercayaan tenant.
- **Solusi jelas:** Field `solution` wajib minimal 5 karakter — tulis langkah penyelesaian yang konkret.
- **Bukti gambar:** Minta tenant melampirkan foto untuk komplain terkait kerusakan fisik (meter bocor, kabel rusak).
- **Notifikasi otomatis:** Pastikan user terkait komplain terisi (`user_id`) agar notifikasi penyelesaian terkirim.

## 8. Hak Akses per Operasi

| Operasi | admin | supervisor | petugas | tenant |
| --- | :---: | :---: | :---: | :---: |
| Lihat daftar komplain | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Buat komplain | ✅ | ✅ | ✅ | ✅ |
| Lihat detail komplain | ✅ | ✅ | ✅ | ✅ (miliknya) |
| Ubah komplain | ✅ | ✅ | ❌ | ❌ |
| Tanggapi (action) | ✅ | ✅ | ❌ | ❌ |
| Hapus komplain | ✅ | ✅ | ❌ | ❌ |

## 9. Referensi Terkait

- [05-operations.md](05-operations.md) — Komplain sering terkait tagihan/pembayaran
- [09-profile-notifications.md](09-profile-notifications.md) — Notifikasi penyelesaian komplain
- [08-audit-logs.md](08-audit-logs.md) — Jejak audit perubahan komplain
