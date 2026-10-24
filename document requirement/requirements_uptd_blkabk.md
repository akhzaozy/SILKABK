# 📋 Software Requirements Specification
## Sistem Informasi Manajemen Kesehatan
### UPTD BLKABK Balikpapan

> **Versi:** 2.0 (Revisi)
> **Metodologi:** Personal Extreme Programming (PXP)
> **Stack:** Laravel · Inertia.js · Vue.js · PostgreSQL
> **Standar:** Permenkes 24/2022 · SDIDTK Kemenkes RI

---

## 📌 Ringkasan Sistem

Sistem ini merupakan aplikasi berbasis web untuk mengelola rekam medis elektronik (EMR), pendaftaran pasien, penjadwalan terapi, dan skrining KPSP untuk anak berkebutuhan khusus di UPTD BLKABK Balikpapan. Sistem menggantikan proses berbasis Google Spreadsheet dan kertas yang tidak tervalidasi dan tidak aman.

---

## 👥 Peran Pengguna (User Roles)

| Role | Deskripsi |
|---|---|
| `super_admin` | Kepala UPTD — akses penuh ke seluruh sistem |
| `admin` | Staf administrasi — kelola data pasien & jadwal |
| `dokter` | Dokter umum/spesialis — akses rekam medis & diagnosa |
| `terapis` | Terapis (OT, TW, BT, FT, SI) — input sesi terapi |
| `psikolog` | Psikolog — asesmen psikologis & skrining |
| `orang_tua` | Wali/orang tua pasien — pantau perkembangan anak |

---

## 🗂️ Modul & Fitur Sistem

---

### 1. 🔐 Modul Autentikasi & Manajemen Akun

#### US-01 · Login & Logout
- [ ] Halaman login dengan validasi kredensial (email + password)
- [ ] Mekanisme logout yang aman (invalidate session)
- [ ] Rate limiting untuk mencegah brute-force login
- [ ] Redirect pasca-login berdasarkan role pengguna

#### US-02 · Role-Based Access Control (RBAC)
- [ ] Middleware otorisasi berbasis role di setiap route
- [ ] Halaman ditampilkan sesuai hak akses role
- [ ] Guard 403 Forbidden untuk akses tidak sah
- [ ] Policy Laravel untuk setiap resource sensitif

#### US-03 · Manajemen Pengguna *(super_admin)*
- [ ] CRUD akun pengguna (nama, email, role, password)
- [ ] Aktivasi / nonaktivasi akun pengguna
- [ ] Reset password oleh admin
- [ ] Tampilan daftar pengguna dengan filter berdasarkan role

---

### 2. 👶 Modul Manajemen Pasien

#### US-04 · Pendaftaran Pasien Baru *(admin)*
- [ ] Form pendaftaran pasien **baru** dengan validasi lengkap:
  - Data anak: nama, tanggal lahir, tempat lahir, jenis kelamin, alamat, diagnosis awal
  - Data wali: nama, hubungan dengan anak (Ayah/Ibu/Wali), nomor telepon, alamat
- [ ] Generate nomor rekam medis otomatis (format unik: `RM-YYYYMMDD-XXXX`)
- [ ] Cegah duplikasi data pasien (cek nama + tanggal lahir + nama wali)

#### US-05 · Pendaftaran Layanan — Pasien Lama *(admin, orang_tua)*

> **Aturan penting:** Pendaftaran layanan hanya bisa dilakukan jika tersedia slot jadwal dari **dokter**, **psikolog**, atau **terapis** yang belum dibooking. Jika semua slot sudah penuh (sudah ada yang memesan), slot tersebut tidak ditampilkan.

**Alur untuk Orang Tua (role: `orang_tua`):**
- [ ] Orang tua memilih anak yang sudah terdaftar (hanya anak milik sendiri)
- [ ] Data anak (nama, tanggal lahir, tempat lahir, alamat) **sudah fix / tidak bisa diubah** — hanya tampil sebagai informasi
- [ ] Orang tua **hanya perlu mengisi keluhan** (chief complaint) saat pendaftaran
- [ ] Sistem menampilkan daftar slot jadwal tersedia (dokter/psikolog/terapis) yang **belum dibooking**
- [ ] Orang tua memilih slot yang diinginkan
- [ ] Orang tua memilih **berapa jam sesi** yang diinginkan (minimal 1 jam, disesuaikan ketersediaan tenaga medis)
- [ ] Setelah booking berhasil, slot tersebut **hilang dari daftar tersedia** untuk pengguna lain

**Alur untuk Admin (role: `admin`):**
- [ ] Admin memilih pasien lama dari daftar pasien terdaftar
- [ ] Data anak ditampilkan otomatis (nama, tanggal lahir, tempat lahir, alamat) — **tidak bisa diubah melalui form pendaftaran**
- [ ] Admin mengisi keluhan pasien
- [ ] Admin memilih slot jadwal tersedia dan jumlah jam sesi
- [ ] Setelah booking berhasil, slot tersebut **hilang dari daftar tersedia**

#### US-06 · Pencarian & Lihat Data Pasien *(admin, dokter, terapis, psikolog, super_admin)*
- [ ] Pencarian pasien berdasarkan nama / nomor RM
- [ ] Tampilan profil lengkap pasien
- [ ] Riwayat kunjungan dan terapi terintegrasi dalam profil

#### US-07 · Edit & Update Data Pasien *(admin)*
- [ ] Edit data demografis pasien (nama, tanggal lahir, tempat lahir, alamat, diagnosis awal)
- [ ] Edit data wali/orang tua
- [ ] Audit log perubahan data (siapa, kapan, apa yang diubah)

#### US-08 · Dashboard Orang Tua *(orang_tua)*
- [ ] Lihat profil **anak yang didaftarkan sendiri** (bukan semua pasien)
- [ ] Lihat jadwal terapi anak yang akan datang
- [ ] Lihat ringkasan perkembangan anak
- [ ] Lihat rekam medis **hanya milik anak sendiri** — tidak bisa melihat rekam medis anak lain
- [ ] Akses rekam medis **read-only** — tidak bisa mengedit

---

### 3. 📅 Modul Penjadwalan Terapi

> **Aturan utama:** Jadwal **hanya bisa dibuat oleh Admin dan Super Admin**. Orang tua, terapis, dokter, dan psikolog **tidak bisa membuat jadwal**.

#### US-09 · Buat & Kelola Jadwal *(admin, super_admin)*
- [ ] Form pembuatan jadwal:
  - Pilih tenaga medis: **Dokter / Psikolog / Terapis**
  - Pilih jenis layanan / terapi
  - Pilih tanggal & jam mulai
  - Tentukan **durasi (per jam)** — contoh: 1 jam, 2 jam, dst. (tidak menggunakan menit tetap)
- [ ] Validasi konflik: tolak jadwal baru jika tenaga medis sudah memiliki jadwal yang overlap pada tanggal & waktu yang sama
- [ ] Status jadwal: `Tersedia` → `Terbooking` → `Berlangsung` → `Selesai` → `Dibatalkan`
- [ ] Edit dan batalkan jadwal yang sudah dibuat (hanya admin & super_admin)
- [ ] Slot yang sudah `Terbooking` **tidak ditampilkan** sebagai pilihan saat orang tua/admin mendaftar layanan

#### US-10 · Lihat Jadwal *(terapis, dokter, psikolog, orang_tua)*
- [ ] Tampilan daftar/kalender jadwal sesuai role:
  - **Terapis / Dokter / Psikolog:** hanya jadwal milik sendiri
  - **Orang Tua:** hanya jadwal anak sendiri
- [ ] Filter jadwal berdasarkan tanggal / status
- [ ] Tampilan info: nama pasien, jenis layanan, jam mulai, durasi, status

---

### 4. 💊 Jenis Terapi & Layanan

> **Revisi:** Jenis terapi diperluas sesuai layanan nyata di UPTD BLKABK Balikpapan.

Jenis terapi & layanan yang tersedia di sistem:

| Kode | Nama Layanan | Deskripsi |
|---|---|---|
| TW | Terapi Wicara | Intervensi gangguan komunikasi & bahasa |
| TO | Terapi Okupasi | Latihan aktivitas kehidupan sehari-hari & motorik halus |
| TF | Terapi Fisik / Fisioterapi | Pemulihan & latihan motorik kasar |
| SI | Sensori Integrasi | Pemrosesan informasi sensorik |
| TB | Terapi Perilaku (ABA) | Applied Behavior Analysis untuk perilaku adaptif |
| PS | Konsultasi Psikologi | Asesmen & intervensi psikologis oleh psikolog |
| DK | Konsultasi Dokter | Pemeriksaan medis & diagnosa oleh dokter |

**Aturan tarif:**
- [ ] Semua layanan dihitung **per jam** (bukan per sesi tetap)
- [ ] Orang tua / admin bebas memilih **berapa jam** sesuai ketersediaan tenaga medis
- [ ] Biaya dihitung: `tarif_per_jam × jumlah_jam`
- [ ] Setiap jenis terapi memiliki field `price_per_hour` (harga per jam)

---

### 5. 📁 Modul Rekam Medis Elektronik (EMR)

> **Aturan akses rekam medis:**
> - **Tenaga medis** (dokter, psikolog, terapis) → dapat melihat **semua rekam medis**
> - **Super Admin & Admin** → dapat melihat **semua rekam medis**
> - **Orang Tua** → hanya dapat melihat rekam medis **anak yang didaftarkan sendiri**, **read-only**
> - Role lain tidak memiliki akses ke rekam medis

#### US-11 · Input Rekam Medis Sesi Terapi *(terapis)*
- [ ] Form SOAP/narasi sesi terapi:
  - Subjective, Objective, Assessment, Plan (SOAP)
  - Catatan perkembangan anak per sesi
  - Tanggal & waktu sesi, durasi (jam)
- [ ] Kunci rekam medis setelah disimpan (tidak bisa diedit sembarangan)
- [ ] Rekam medis terhubung ke jadwal terapi yang sesuai

#### US-12 · Input Rekam Medis Medis/Klinis *(dokter)*
- [ ] Form diagnosa: ICD-10 coding
- [ ] Anamnesis, pemeriksaan fisik, diagnosa kerja, diagnosa banding
- [ ] Rencana tatalaksana & rujukan
- [ ] Rekomendasi / catatan klinis

#### US-13 · Input Rekam Medis Psikologi *(psikolog)*
- [ ] Form asesmen psikologis
- [ ] Hasil skrining KPSP (jika berlaku)
- [ ] Rekomendasi intervensi

#### US-14 · Lihat Riwayat Rekam Medis

| Siapa | Akses |
|---|---|
| Dokter, Psikolog, Terapis | Semua rekam medis (bisa lihat semua pasien) |
| Super Admin, Admin | Semua rekam medis (bisa lihat semua pasien) |
| Orang Tua | **Hanya rekam medis anak sendiri** — read-only |

- [ ] Timeline rekam medis pasien secara kronologis
- [ ] Filter berdasarkan jenis catatan (terapi / medis / psikologi)
- [ ] Orang tua hanya melihat rekam medis anak yang terdaftar atas namanya

---

### 6. 🧪 Modul Skrining KPSP

#### US-15 · Pelaksanaan Skrining KPSP *(dokter, psikolog)*
- [ ] Form skrining KPSP sesuai usia anak (3–72 bulan):
  - 10 pertanyaan per kelompok usia
  - Pilihan jawaban: Ya / Tidak / Dengan Bantuan
- [ ] Kalkulasi skor otomatis
- [ ] Output hasil: `Sesuai`, `Meragukan`, `Penyimpangan`
- [ ] Interpretasi hasil manual oleh tenaga profesional
- [ ] Simpan hasil skrining ke rekam medis pasien

---

### 7. 💰 Modul Pembayaran

#### US-16 · Pencatatan Pembayaran *(admin)*
- [ ] Catat pembayaran berdasarkan booking layanan
- [ ] Kalkulasi otomatis: `tarif_per_jam × jumlah_jam`
- [ ] Metode pembayaran: `Cash` / `Transfer`
- [ ] Generate kode pembayaran unik: `PAY-YYYYMMDD-XXXX`
- [ ] Status: `Belum Bayar` / `Lunas`

#### US-17 · Riwayat Pembayaran *(admin, orang_tua)*
- [ ] Admin: lihat semua riwayat pembayaran
- [ ] Orang tua: hanya riwayat pembayaran anak sendiri
- [ ] Filter berdasarkan tanggal / status / pasien

---

### 8. 📊 Modul Laporan & Rekap Data

#### US-18 · Laporan & Rekap *(super_admin, admin)*
- [ ] Laporan statistik pasien: total pasien aktif, pasien baru per bulan
- [ ] Distribusi jenis layanan/terapi yang paling banyak digunakan
- [ ] Laporan aktivitas terapi per periode
- [ ] Laporan hasil skrining KPSP agregat
- [ ] Export laporan ke format PDF / Excel
- [ ] Filter laporan: harian, mingguan, bulanan, tahunan

---

## ⚙️ Requirement Non-Fungsional

### 🔒 Keamanan
- [ ] Autentikasi menggunakan session-based Laravel
- [ ] HTTPS pada seluruh komunikasi (production)
- [ ] Validasi input di sisi server (Laravel Form Request)
- [ ] Proteksi CSRF pada semua form
- [ ] Log aktivitas pengguna (audit trail) untuk rekam medis

### 📱 Usability
- [ ] Antarmuka responsif (desktop & tablet)
- [ ] Bahasa antarmuka: Bahasa Indonesia
- [ ] Navigasi intuitif sesuai role pengguna
- [ ] Pesan error yang jelas dan informatif
- [ ] Loading state pada operasi async

### ⚡ Performa
- [ ] Waktu respons halaman < 3 detik (kondisi jaringan normal)
- [ ] Pagination pada daftar data yang panjang
- [ ] Query database yang dioptimasi (Eager Loading, Index)

### 📄 Kepatuhan Regulasi
- [ ] Struktur rekam medis mengikuti **Permenkes No. 24 Tahun 2022**
- [ ] Skrining KPSP mengikuti standar **SDIDTK Kemenkes RI**
- [ ] Sistem dikategorikan sebagai **EMR** (satu fasilitas)

---

## 🗃️ Entitas Data Utama (Revisi)

```
users                → Akun semua role
parent_guardians     → Profil orang tua/wali (terhubung ke users)
therapists           → Profil terapis (terhubung ke users)
doctors              → Profil dokter (terhubung ke users)
psychologists        → Profil psikolog (terhubung ke users)  ← BARU
patients             → Data pasien anak
therapy_types        → Jenis terapi & layanan (dengan price_per_hour)
registrations        → Pendaftaran layanan (pasien baru / lama)
schedules            → Slot jadwal yang dibuat admin (dengan durasi per jam)
payments             → Pencatatan pembayaran
therapy_results      → Rekam medis sesi terapi (terapis)
medical_records      → Rekam medis klinis (dokter)
psychology_records   → Rekam medis psikologi (psikolog)  ← BARU
```

---

## 📐 Prioritas Fitur (MoSCoW)

| Prioritas | Fitur |
|---|---|
| **Must Have** | Autentikasi & RBAC, Pendaftaran Pasien Baru & Lama, Penjadwalan (admin-only), EMR (terapi/medis/psikologi), Akses kontrol rekam medis |
| **Should Have** | Dashboard Orang Tua, Pembayaran per jam, Laporan Dasar, Skrining KPSP |
| **Could Have** | Export PDF/Excel, Audit Log, Notifikasi In-App |
| **Won't Have** | DSS Otomatis, Integrasi BPJS, Mobile App, Payment Gateway |

---

## 🔁 Iterasi PXP (Revisi)

| Iterasi | User Stories | Fokus |
|---|---|---|
| 1 | US-01, US-02, US-03 | Autentikasi & RBAC |
| 2 | US-04, US-05, US-06, US-07 | Manajemen Pasien (Baru & Lama) |
| 3 | US-09, US-10 | Penjadwalan (Admin-only, slot per jam) |
| 4 | US-11, US-12, US-13, US-14 | Rekam Medis Elektronik + Kontrol Akses |
| 5 | US-15 | Skrining KPSP |
| 6 | US-08, US-16, US-17, US-18 | Dashboard Ortu, Pembayaran & Laporan |

---

## 🚫 Di Luar Scope Sistem

- ❌ Automated decision / rekomendasi dari hasil KPSP (interpretasi manual)
- ❌ SAW atau metode scoring otomatis
- ❌ Notifikasi email / SMS
- ❌ Payment gateway (hanya pencatatan, bukan transaksi online)
- ❌ Upload foto / attachment
- ❌ Multi-facility / EHR
- ❌ REST API terpisah (Inertia sebagai jembatan)

---

*Dokumen ini merupakan bagian dari Skripsi — UPTD BLKABK Balikpapan.*
*Metodologi: Personal Extreme Programming (PXP)*
*Versi 2.0 — Direvisi berdasarkan klarifikasi kebutuhan sistem*
