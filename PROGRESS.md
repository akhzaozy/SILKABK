# Progress & Next Step — TA Bismillah
> Sistem Manajemen Klinik Tumbuh Kembang Anak  
> Terakhir diperbarui: 2026-05-11

---

## Stack
- **Backend** : Laravel 11 + PostgreSQL
- **Frontend** : Vue 3 + Inertia.js + Tailwind CSS
- **Server**   : XAMPP (Windows), Timezone WITA (Asia/Makassar, UTC+8)

---

## Role Sistem
| Role | Akses |
|---|---|
| `super_admin` | Semua fitur + kelola akun |
| `admin` | Pasien, pendaftaran, jadwal, pembayaran, laporan |
| `terapis` | Jadwal sendiri + input hasil terapi |
| `dokter` | Rekam medis + screening KPSP |
| `psikolog` | Asesmen psikologi + screening KPSP |
| `orang_tua` | Data anak, booking, jadwal anak, pembayaran anak |

---

## Fitur yang Sudah Selesai ✅

### Auth
- [x] Login / Logout
- [x] Register (publik)
- [x] Middleware role & is_active check

### Dashboard
- [x] Statistik per role (admin, orang_tua, terapis, dokter, psikolog)

### Manajemen Akun (Super Admin)
- [x] CRUD user semua role
- [x] Toggle aktif/nonaktif akun
- [x] Auto-buat profil Therapist saat buat user `terapis`
- [x] Auto-buat profil Psychologist saat buat user `psikolog`

### Manajemen Pasien (Admin)
- [x] CRUD pasien
- [x] Nomor rekam medis otomatis

### Pendaftaran (US-05)
- [x] Orang tua booking slot terapi
- [x] Admin buat pendaftaran manual
- [x] Admin approve / reject pendaftaran
- [x] Setelah approve → slot otomatis berubah ke `scheduled`
- [x] Setelah reject → slot kembali tersedia

### Jadwal (US-10)
- [x] Admin generate slot mingguan otomatis (template Senin–Sabtu 08:00–12:00)
- [x] Admin buat jadwal manual
- [x] Halaman jadwal per terapis / dokter / psikolog
- [x] Cancel jadwal

### Pembayaran (US-07, US-08)
- [x] Admin catat pembayaran
- [x] Admin lihat riwayat pembayaran
- [x] Orang tua lihat riwayat & bukti bayar anak

### Hasil Terapi (US-13)
- [x] Terapis input hasil sesi terapi
- [x] Terapis edit hasil sesi

### Rekam Medis (US-14)
- [x] Dokter CRUD rekam medis + KPSP
- [x] Psikolog CRUD asesmen psikologi + KPSP

### Laporan & Export
- [x] Laporan summary + grafik (admin)
- [x] Export CSV: pasien, pendaftaran, pembayaran, jadwal

### Profil
- [x] Edit profil untuk semua role
- [x] Ganti password

---

## Bug yang Sudah Diperbaiki ✅

| # | Bug | Solusi |
|---|---|---|
| 1 | Timezone UTC menyebabkan filter jam slot tidak akurat | Ganti ke `Asia/Makassar` di `config/app.php` |
| 2 | Filter waktu slot berlaku juga di sisi admin (hapus riwayat) | Filter waktu HANYA di `createByParent()`, admin lihat semua slot |
| 3 | `cancelExpiredSlots()` mengubah status DB dan merusak riwayat | Dihapus — filter cukup di query SELECT, tidak ubah data |
| 4 | `ReportController` pakai `DATE_FORMAT` (MySQL syntax) | Sudah pakai `TO_CHAR` (PostgreSQL) |
| 5 | `UserController` tidak buat record `ParentGuardian` untuk role `orang_tua` | Tambah `syncParentGuardian()` di `store()` & `update()` |
| 6 | `UserController` tidak buat record `Doctor` untuk role `dokter` | Tambah `syncDoctor()` di `store()` & `update()` |
| 7 | `UserController::update()` tidak update nama di tabel profil ketika nama user diubah | `syncProfile()` sekarang update nama di semua tabel profil |
| 8 | `edit()` hanya load relasi `therapist`, relasi lain tidak dimuat | Load semua relasi: `therapist`, `doctor`, `psychologist`, `parentGuardian` |

---

## Bug yang Masih Ada ⚠️

### SEDANG — Perlu diperbaiki

**3. Slot masa depan tidak otomatis tersedia — admin harus generate manual tiap minggu**
- Tidak ada notifikasi/reminder ke admin saat slot habis
- Rekomendasi: Tambah Laravel Scheduler (`php artisan schedule:run`) yang auto-generate slot mingguan, atau minimal tampilkan peringatan di dashboard admin

**4. ProfileController tidak bisa mengosongkan field**
- Middleware `ConvertEmptyStringsToNull` + operator `??` menyebabkan field seperti `specialization` dan `phone` tidak bisa dihapus (selalu fallback ke nilai lama)

---

## Next Step 🚀

### Prioritas 1 — Fix Bug Kritis ✅ SELESAI
- [x] ~~Fix UserController: tambah `ParentGuardian::create()` dan `Doctor::create()`~~
- [ ] **Test full flow**: buat user orang_tua → login → booking slot → admin approve → cek jadwal anak
- [ ] **Test dokter flow**: buat user dokter → login → buat rekam medis
- [ ] **Fix data lama**: user `orang_tua` / `dokter` yang sudah ada sebelum perbaikan ini perlu di-edit ulang lewat admin (buka edit → simpan) agar record profil terbuat

### Prioritas 2 — Fitur Tambahan (estimasi: 3–5 jam)
- [ ] **Notifikasi slot habis**: tampilkan warning di dashboard admin jika tidak ada slot available untuk 7 hari ke depan
- [ ] **Auto-generate slot**: tambah artisan command + scheduler untuk generate slot mingguan otomatis
- [ ] **Konfirmasi email/notifikasi**: kirim notifikasi ke orang tua saat pendaftaran di-approve/reject (opsional)

### Prioritas 3 — Polish & QA (estimasi: 2–4 jam)
- [ ] **Seeding data demo**: seeder lengkap dengan data realistis (pasien, jadwal, pembayaran)
- [ ] **Test semua role** end-to-end di browser
- [ ] **Cek responsive** tampilan mobile
- [ ] **Error handling**: pastikan semua 403/422 tampil pesan yang user-friendly (bukan blank page)
- [ ] **Validasi form tambahan**: orang tua tidak bisa booking slot yang sama dua kali untuk pasien yang sama

### Prioritas 4 — Deployment (estimasi: 2–3 jam)
- [ ] Setup environment production
- [ ] Konfigurasi `.env` production (DB, timezone, app_url)
- [ ] `php artisan optimize`
- [ ] Setup HTTPS (jika perlu)
- [ ] Buat user admin/super_admin awal via seeder

---

## Catatan Teknis Penting
- **Database**: PostgreSQL — gunakan `TO_CHAR()` bukan `DATE_FORMAT()`, gunakan `ILIKE` bukan `LIKE` untuk case-insensitive
- **Timezone**: semua `now()` dan `today()` sudah WITA (UTC+8, Balikpapan)
- **Slot jadwal**: Admin harus generate slot tiap minggu via menu **Jadwal → Generate Slot**
- **Inertia form**: gunakan `form.put()` atau `form.post()`, bukan `form.submit()` langsung
