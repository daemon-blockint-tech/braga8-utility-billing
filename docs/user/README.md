# User Guide

Panduan pengguna untuk aplikasi **Braga8 Utility Billing** — sistem manajemen tagihan utilitas (listrik, air, gas) untuk properti sewa Braga8.

Dokumen ini ditujukan untuk pengguna operasional aplikasi (admin, supervisor, petugas) dan menjelaskan alur kerja, hak akses per peran, serta langkah-langkah menggunakan setiap modul.

## Daftar Isi

| # | Dokumen | Deskripsi |
| --- | --------- | ----------- |
| 01 | [Getting Started](01-getting-started.md) | Login, verifikasi email, dan navigasi umum |
| 02 | [Roles & Access](02-roles-and-access.md) | Hak akses tiap peran (admin, supervisor, petugas, tenant) |
| 03 | [Dashboard](03-dashboard.md) | Ringkasan metrik dan widget dashboard |
| 04 | [Master Data](04-master-data.md) | Manajemen Users, Tenants, Units, Utility Meters, Tariffs |
| 05 | [Operations](05-operations.md) | Meter Readings, Invoices, Payments, Reminders |
| 06 | [Complaints](06-complaints.md) | Pengelolaan komplain tenant |
| 07 | [Reports](07-reports.md) | Laporan penggunaan utilitas |
| 08 | [Audit Logs](08-audit-logs.md) | Jejak audit sistem |
| 09 | [Profile & Notifications](09-profile-notifications.md) | Profil pengguna dan notifikasi |

## Konvensi

- **Bahasa**: Antarmuka aplikasi menggunakan Bahasa Indonesia. Istilah UI dipertahankan dalam bahasa Indonesia dan diberi padanan Inggris pada penjelasan.
- **Peran (Role)**: `admin`, `supervisor`, `petugas`, `tenant`. Lihat [02-roles-and-access.md](02-roles-and-access.md) untuk detail.
- **Ikon**: Aplikasi menggunakan Font Awesome. Tombol aksi menggunakan ikon standar (✏️ edit, 🗑️ hapus, 👁️ lihat, 📄 PDF).

## Catatan Keamanan

> ⚠️ Pemeriksaan peran dilakukan di dalam masing-masing controller. Beberapa rute resource memiliki celah BOLA (Broken Object Level Authorization) yang tercatat pada `SECURITY_AUDIT_REPORT.md`. Panduan ini mendokumentasikan **perilaku yang dimaksud** (intended behaviour); pastikan pengguna hanya mengakses data sesuai perannya.
