# CONTEXT.md — Sistem Informasi UPTD BLKABK Balikpapan

> **Baca file ini terlebih dahulu sebelum membantu pengembangan.**
> Berisi seluruh konteks proyek: tujuan, arsitektur, role, user story, struktur database, dan status pengerjaan.

---

## 1. Identitas Proyek

| Atribut | Detail |
|---|---|
| **Nama Sistem** | Sistem Informasi Manajemen UPTD BLKABK Balikpapan |
| **Jenis Sistem** | EMR (Electronic Medical Record) — single facility |
| **Instansi** | UPTD Balai Layanan Anak Berkebutuhan Khusus, Balikpapan |
| **Jenis Tugas** | Tugas Akhir (Skripsi) — Pengembangan Solo |
| **Metodologi** | Personal Extreme Programming (PXP) |
| **Timeline** | 20 Minggu · 5 Iterasi |
| **Standar Acuan** | Permenkes No. 24 Tahun 2022 (Rekam Medis Elektronik), SDIDTK Kemenkes |

---

## 2. Latar Belakang & Masalah

UPTD BLKABK saat ini menggunakan:
- **Google Spreadsheet** untuk pendaftaran pasien → rentan kerusakan data, tidak aman
- **Kertas** untuk rekam medis → double entry, sulit dilacak
- Tidak ada sistem terintegrasi antar bagian (admin, terapis, dokter)
- Tidak sesuai standar Permenkes 24/2022 dan SDIDTK

**Solusi:** Sistem informasi berbasis web yang mengintegrasikan pendaftaran pasien, pembayaran, penjadwalan terapi, dan rekam medis elektronik.

---

## 3. Tech Stack

```
Backend  : Laravel 11 (PHP 8.2+)
Frontend : Vue.js 3 (Composition API)
Bridge   : Inertia.js (menghubungkan Laravel ↔ Vue tanpa REST API terpisah)
Database : PostgreSQL (standalone, bukan via Laragon/XAMPP)
Styling  : Tailwind CSS
State    : Pinia
Routing  : Vue Router + Inertia Link
ORM      : Eloquent Laravel
Auth     : Laravel built-in session authentication
```

---

## 4. Arsitektur Sistem

```
Browser (Vue SPA)
    ↕  Inertia.js (bukan REST API — server-side renders props ke Vue)
Laravel (Controller → Model → Eloquent)
    ↕
PostgreSQL
```

**Pola Inertia yang digunakan:**
- Controller return `Inertia::render('NamaPage', ['props' => $data])`
- Vue page menerima props via `defineProps()`
- Navigasi menggunakan `<Link>` dari `@inertiajs/vue3`
- Form submit menggunakan `useForm()` dari Inertia

---

## 5. Role & Hak Akses

| Role | Slug | Akses |
|---|---|---|
| **Super Admin** | `super_admin` | Akses penuh ke seluruh sistem, bypass semua middleware |
| **Admin** | `admin` | Kelola pasien, laporan pembayaran, jadwal terapi |
| **Orang Tua** | `orang_tua` | Daftar layanan anak, lihat jadwal & riwayat bayar & rekam medis anak sendiri |
| **Terapis** | `terapis` | Lihat jadwal milik sendiri, input data/hasil terapi per sesi |
| **Dokter** | `dokter` | Input rekam medis elektronik, screening KPSP |

### Aturan Ownership Penting

- **Orang Tua** hanya bisa akses data anak **milik sendiri** (enforced via `OwnershipPolicy`)
- **Orang Tua tidak punya route** untuk edit/delete jadwal (bukan sekadar dibatasi — route-nya memang tidak ada)
- **Terapis** hanya bisa isi hasil terapi untuk jadwal yang `therapist_id`-nya sesuai dengan profilnya
- **Dokter** hanya bisa edit rekam medis yang `doctor_id`-nya sesuai dengan profilnya
- **Super Admin** di-bypass via `Gate::before()` di `AuthServiceProvider` — tidak perlu dicek manual di setiap controller

---

## 6. User Story

| ID | Role | Fitur | Iterasi | Status |
|---|---|---|---|---|
| US-01 | Semua | Login Sistem | 1 | ✅ Selesai |
| US-02 | Semua | Logout Sistem | 1 | ✅ Selesai |
| US-03 | Super Admin | Kelola Akun Pengguna | 1 | ✅ Selesai |
| US-04 | Admin | Pendaftaran Pasien | 2 | 🔄 Belum |
| US-05 | Orang Tua | Pendaftaran Layanan | 2 | 🔄 Belum |
| US-06 | Admin | Kelola Data Pasien | 2 | 🔄 Belum |
| US-07 | Admin | Pencatatan Pembayaran | 3 | ✅ Selesai |
| US-08 | Admin | Riwayat Pembayaran | 3 | ✅ Selesai |
| US-09 | Orang Tua | Lihat Bukti Pembayaran | 3 | ✅ Selesai |
| US-10 | Admin | Kelola Jadwal Terapi | 4 | 🔄 Belum |
| US-11 | Orang Tua | Lihat Jadwal Terapi | 4 | 🔄 Belum |
| US-12 | Terapis | Lihat Jadwal Terapis | 4 | 🔄 Belum |
| US-13 | Terapis | Catat Hasil Terapi | 5 | 🔄 Belum |
| US-14 | Dokter | Kelola Rekam Medis | 5 | 🔄 Belum |

---

## 7. Rencana Iterasi PXP

Setiap iterasi mengikuti 5 fase: **Initialization → Design → Implementation → Testing → Retrospective**

### Iterasi 1 — Autentikasi & Manajemen Akun (Minggu 1–3) ✅
- US-01 Login, US-02 Logout, US-03 Kelola Pengguna
- File: `AuthController`, `UserController`, `RoleMiddleware`, `Login.vue`, `Users/*`

### Iterasi 2 — Manajemen Pasien (Minggu 4–7) 🔄
- US-04 Daftar Pasien (Admin), US-05 Daftar Layanan (Orang Tua), US-06 Kelola Pasien
- File yang perlu dibuat: `PatientController`, `RegistrationController`, model `Patient`, `ParentGuardian`, `Registration`
- Vue: `Patients/*`, `Parent/Register.vue`, `Registrations/*`

### Iterasi 3 — Pembayaran (Minggu 8–11) ✅
- US-07 Catat Bayar, US-08 Riwayat Bayar, US-09 Bukti Bayar Orang Tua
- File: `PaymentController`, model `Payment`, `Payments/*`, `Parent/Payments.vue`

### Iterasi 4 — Penjadwalan Terapi (Minggu 12–15) 🔄
- US-10 Kelola Jadwal (Admin), US-11 Lihat Jadwal (Orang Tua), US-12 Jadwal Terapis
- File yang perlu dibuat: migration `schedules`, `ScheduleController` (sebagian sudah ada), `Schedules/*`, `Therapist/Schedules.vue`

### Iterasi 5 — Rekam Medis (Minggu 16–19) 🔄
- US-13 Hasil Terapi (Terapis), US-14 Rekam Medis (Dokter)
- File yang perlu dibuat: migration `medical_records`, `therapy_results`, `MedicalRecordController`, `TherapyResultController`, `Doctor/*`, `Therapist/Results/*`

**Minggu 20:** Finalisasi, pengujian akhir, penyusunan laporan

---

## 8. Struktur Database (12 Tabel)

### Urutan Migration (penting — jangan diubah urutan FK-nya)

```
1.  create_roles_table
2.  create_users_table               ← FK: roles
3.  create_parent_guardians_table    ← FK: users
4.  create_therapists_table          ← FK: users
5.  create_doctors_table             ← FK: users
6.  create_therapy_types_table
7.  create_patients_table            ← FK: parent_guardians
8.  create_registrations_table       ← FK: patients, therapy_types
9.  create_schedules_table           ← FK: patients, therapists, therapy_types, users
10. create_payments_table            ← FK: patients, schedules, users
11. create_therapy_results_table     ← FK: schedules, therapists, patients
12. create_medical_records_table     ← FK: patients, doctors
```

### Skema Tabel Lengkap

#### `roles`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(50) UNIQUE | Nama role |
| slug | varchar(50) UNIQUE | Digunakan di middleware |
| timestamps | | |

**Seeder:** `super_admin`, `admin`, `orang_tua`, `terapis`, `dokter`

---

#### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| role_id | FK → roles | |
| name | varchar(100) | |
| email | varchar(100) UNIQUE | Digunakan untuk login |
| password | varchar(255) | bcrypt |
| is_active | boolean DEFAULT true | Akun nonaktif tidak bisa login |
| remember_token | varchar(100) NULLABLE | |
| timestamps | | |

---

#### `parent_guardians`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users (One-to-One) | |
| name | varchar(100) | |
| relationship | varchar(50) | `Ayah` / `Ibu` / `Wali` |
| phone | varchar(20) | |
| address | text NULLABLE | |
| timestamps | | |

---

#### `therapists`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users (One-to-One) | |
| name | varchar(100) | |
| specialization | varchar(100) | |
| phone | varchar(20) NULLABLE | |
| is_active | boolean DEFAULT true | |
| timestamps | | |

---

#### `doctors`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users (One-to-One) | |
| name | varchar(100) | |
| specialization | varchar(100) NULLABLE | |
| license_number | varchar(50) NULLABLE | Nomor SIP |
| phone | varchar(20) NULLABLE | |
| timestamps | | |

---

#### `therapy_types`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) UNIQUE | |
| description | text NULLABLE | |
| duration_minutes | integer DEFAULT 60 | |
| timestamps | | |

**Seeder:** Terapi Perilaku (60m), Terapi Okupasi (60m), Terapi Wicara (45m), Sensori Integrasi (60m)

---

#### `patients`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| guardian_id | FK → parent_guardians | |
| medical_record_number | varchar(20) UNIQUE | Nomor rekam medis |
| name | varchar(100) | |
| gender | varchar(10) | `L` / `P` |
| birth_date | date | |
| birth_place | varchar(100) | |
| address | text | |
| diagnosis | text NULLABLE | Diagnosis awal |
| notes | text NULLABLE | |
| is_active | boolean DEFAULT true | |
| timestamps | | |

---

#### `registrations`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| patient_id | FK → patients | |
| therapy_type_id | FK → therapy_types | |
| registration_date | date | |
| status | varchar(20) DEFAULT `pending` | `pending` / `approved` / `rejected` |
| notes | text NULLABLE | |
| timestamps | | |

---

#### `schedules`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| patient_id | FK → patients | |
| therapist_id | FK → therapists | |
| therapy_type_id | FK → therapy_types | |
| schedule_date | date | |
| start_time | time | |
| end_time | time | |
| status | varchar(20) DEFAULT `scheduled` | `scheduled` / `done` / `cancelled` |
| notes | text NULLABLE | |
| created_by | FK → users | Admin yang membuat |
| timestamps | | |

> **Validasi konflik:** Tolak jadwal baru jika `therapist_id` sama, `schedule_date` sama, dan rentang `start_time`–`end_time` overlap.

---

#### `payments`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| patient_id | FK → patients | |
| schedule_id | FK → schedules NULLABLE | |
| payment_code | varchar(30) UNIQUE | Auto-generate: `PAY-YYYYMMDD-XXXX` |
| amount | numeric(12,2) | |
| payment_date | date | |
| payment_method | varchar(50) | `Cash` / `Transfer` |
| status | varchar(20) DEFAULT `unpaid` | `unpaid` / `paid` |
| notes | text NULLABLE | |
| created_by | FK → users | Admin yang mencatat |
| timestamps | | |

---

#### `therapy_results`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| schedule_id | FK → schedules (One-to-One) | |
| therapist_id | FK → therapists | |
| patient_id | FK → patients | |
| session_date | date | |
| activities | text | Aktivitas yang dilakukan |
| patient_response | text | Respons / perkembangan pasien |
| goals_achieved | text NULLABLE | Target yang tercapai |
| next_plan | text NULLABLE | Rencana sesi berikutnya |
| timestamps | | |

---

#### `medical_records`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| patient_id | FK → patients | |
| doctor_id | FK → doctors | |
| visit_date | date | |
| chief_complaint | text | Keluhan utama |
| physical_examination | text NULLABLE | |
| diagnosis | text | |
| icd_code | varchar(20) NULLABLE | Kode ICD-10 |
| treatment_plan | text NULLABLE | |
| kpsp_result | **jsonb** NULLABLE | Hasil screening KPSP (format JSON) |
| notes | text NULLABLE | |
| timestamps | | |

> **KPSP:** Disimpan sebagai `jsonb`. Interpretasi dilakukan **manual oleh dokter** — tidak ada automated decision. Format JSON: `{ usia_bulan, tanggal_screening, pertanyaan: [{nomor, jawaban}], skor, interpretasi }`

---

## 9. Struktur Folder Project

```
laravel-project/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php           ← US-01, US-02
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php           ← US-03
│   │   │   ├── PatientController.php        ← US-04, US-06   [BELUM]
│   │   │   ├── RegistrationController.php   ← US-05          [BELUM]
│   │   │   ├── PaymentController.php        ← US-07, US-08, US-09
│   │   │   ├── ScheduleController.php       ← US-10, US-11, US-12
│   │   │   ├── TherapyResultController.php  ← US-13
│   │   │   └── MedicalRecordController.php  ← US-14
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php
│   │       ├── CheckIsActive.php
│   │       └── HandleInertiaRequests.php    ← share auth.user & flash ke semua Vue
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Patient.php                      [BELUM]
│   │   ├── ParentGuardian.php               [BELUM]
│   │   ├── Therapist.php                    [BELUM]
│   │   ├── Doctor.php                       [BELUM]
│   │   ├── TherapyType.php                  [BELUM]
│   │   ├── Registration.php                 [BELUM]
│   │   ├── Schedule.php                     [BELUM]
│   │   ├── Payment.php
│   │   ├── TherapyResult.php                [BELUM]
│   │   └── MedicalRecord.php               [BELUM]
│   │
│   ├── Policies/
│   │   └── OwnershipPolicy.php              ← ownership enforcement orang tua
│   │
│   └── Providers/
│       └── AuthServiceProvider.php          ← Gate::before super_admin bypass
│
├── bootstrap/
│   └── app.php                              ← daftar middleware alias (Laravel 11)
│
├── database/
│   ├── migrations/                          ← ikuti urutan di bagian 8
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       └── UserSeeder.php                   ← default: superadmin@uptdblkabk.id / password
│
├── routes/
│   └── web.php                              ← semua route RBAC
│
└── resources/
    └── js/
        ├── app.js                           ← entry point Inertia
        ├── composables/
        │   ├── useAuth.js                   ← akses auth.user dari mana saja
        │   └── useFlash.js                  ← akses flash message
        ├── layouts/
        │   └── AppLayout.vue               ← sidebar role-based
        ├── components/
        │   ├── FormField.vue               ← wrapper label + error + hint
        │   ├── NavItem.vue
        │   ├── NavGroup.vue
        │   ├── StatCard.vue
        │   ├── SummaryCard.vue
        │   ├── DetailRow.vue
        │   └── QuickLink.vue
        └── pages/
            ├── Auth/
            │   └── Login.vue
            ├── Dashboard/
            │   └── Index.vue
            ├── Users/                       ← Super Admin
            │   ├── Index.vue
            │   ├── Create.vue
            │   └── Edit.vue
            ├── Patients/                    [BELUM] ← Admin
            ├── Registrations/               [BELUM] ← Admin
            ├── Payments/                    ← Admin
            │   ├── Index.vue
            │   ├── Create.vue
            │   ├── Show.vue
            │   └── Edit.vue
            ├── Schedules/                   [BELUM] ← Admin
            ├── Parent/                      ← Orang Tua
            │   ├── Payments.vue
            │   ├── PaymentDetail.vue
            │   ├── Schedules.vue            [BELUM]
            │   ├── ScheduleDetail.vue       [BELUM]
            │   ├── MedicalRecords.vue       [BELUM]
            │   └── MedicalRecordDetail.vue  [BELUM]
            ├── Therapist/                   ← Terapis
            │   ├── Schedules.vue            [BELUM]
            │   ├── ScheduleDetail.vue       [BELUM]
            │   └── Results/                 [BELUM]
            └── Doctor/                      ← Dokter
                └── MedicalRecords/          [BELUM]
```

---

## 10. Sistem RBAC

### Middleware

```php
// bootstrap/app.php
$middleware->alias([
    'role'         => RoleMiddleware::class,   // ->middleware('role:admin,super_admin')
    'check.active' => CheckIsActive::class,    // cek is_active setiap request
]);
```

### RoleMiddleware — Aturan Super Admin

```php
// Super Admin selalu bypass — tidak perlu ditambahkan ke setiap route list
if ($userSlug === 'super_admin') {
    return $next($request);
}
```

### Gate & Policy

```php
// AuthServiceProvider — Gate::before untuk super_admin
Gate::before(function ($user, $ability) {
    if ($user->hasRole('super_admin')) return true;
});

// Gate yang tersedia untuk ownership check:
Gate::define('view-patient', ...)
Gate::define('view-schedule', ...)
Gate::define('modify-schedule', ...)   // selalu false untuk orang_tua
Gate::define('view-payment', ...)
Gate::define('view-registration', ...)
Gate::define('view-medical-record', ...)

// Penggunaan di controller:
Gate::authorize('view-schedule', $schedule);   // lempar 403 jika gagal
// atau:
abort_unless(Gate::allows('view-payment', $payment), 403);
```

### Route Groups

```
/users/*               → role:super_admin
/patients/*            → role:admin,super_admin
/registrations/*       → role:admin,super_admin
/payments/*            → role:admin,super_admin
/schedules/*           → role:admin,super_admin
/parent/*              → role:orang_tua,super_admin  + ownership check di controller
/therapist/*           → role:terapis,super_admin   + ownership check di controller
/doctor/*              → role:dokter,super_admin
```

---

## 11. Shared Props via Inertia

Data yang selalu tersedia di **semua Vue page** via `usePage().props`:

```js
// useAuth.js — gunakan ini, bukan akses props langsung
import { useAuth } from '@/composables/useAuth'
const { user, role, isSuperAdmin, isAdmin, isOrangTua, isTerapis, isDokter } = useAuth()

// Data yang tersedia:
// auth.user.id, auth.user.name, auth.user.email, auth.user.is_active
// auth.user.role.id, auth.user.role.name, auth.user.role.slug

// Flash messages:
import { useFlash } from '@/composables/useFlash'
const { success, error } = useFlash()
```

---

## 12. Konvensi Kode

### PHP / Laravel
- Controller method naming: `index`, `create`, `store`, `show`, `edit`, `update` untuk admin; suffix `ByParent` / `ByTherapist` untuk role-specific
- Validasi selalu di controller menggunakan `$request->validate()`
- Pesan error validasi selalu dalam Bahasa Indonesia
- Query filter menggunakan `ilike` (PostgreSQL, case-insensitive) bukan `like`
- Pagination: `->paginate(15)` untuk admin, `->paginate(10)` untuk user biasa
- Flash message: `->with('success', '...')` atau `->with('error', '...')`

### Vue / Inertia
- Semua page menggunakan `AppLayout.vue` sebagai wrapper utama
- Form menggunakan `useForm()` dari `@inertiajs/vue3`
- Navigasi menggunakan `<Link>` bukan `<a>` atau `<router-link>`
- Format tanggal: `toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })`
- Format rupiah: `'Rp ' + Number(val).toLocaleString('id-ID')`
- Tailwind class untuk input standar: `form-input` (lihat komponen Create/Edit)
- Semua teks UI dalam Bahasa Indonesia

### Database
- Gunakan `ilike` bukan `like` untuk pencarian teks (PostgreSQL case-insensitive)
- Tipe `jsonb` untuk `kpsp_result` di `medical_records`
- `payment_code` di-generate otomatis via model `booted()` — jangan diisi manual
- Status enum: gunakan string bukan integer (contoh: `'paid'`, `'unpaid'`, `'scheduled'`)

---

## 13. Hal yang TIDAK Ada di Sistem Ini

Jangan tambahkan atau sarankan fitur berikut — di luar scope:

- ❌ Automated decision / rekomendasi dari hasil KPSP (interpretasi manual oleh dokter)
- ❌ SAW (Simple Additive Weighting) atau metode scoring otomatis
- ❌ Notifikasi email / SMS
- ❌ Payment gateway (hanya pencatatan, bukan transaksi online)
- ❌ Upload foto / attachment (belum dalam scope)
- ❌ Multi-facility / EHR (sistem ini EMR single-facility)
- ❌ REST API terpisah (Inertia digunakan sebagai jembatan, bukan SPA + API)

---

## 14. Cara Menjalankan Project

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi .env untuk PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uptd_blkabk
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Aktifkan extension PHP (php.ini)
extension=pdo_pgsql
extension=pgsql

# Jalankan migration + seeder
php artisan migrate
php artisan db:seed

# Login default
# Email   : superadmin@uptdblkabk.id
# Password: password

# Jalankan server
php artisan serve
npm run dev
```

---

## 15. Perintah Berguna saat Development

```bash
# Clear semua cache (wajib setelah perubahan config/route)
php artisan optimize:clear

# Cek semua route yang terdaftar
php artisan route:list

# Cek status migration
php artisan migrate:status

# Rollback migration terakhir
php artisan migrate:rollback

# Jalankan seeder ulang
php artisan db:seed --class=RoleSeeder

# Cek error log Laravel
tail -f storage/logs/laravel.log
```

---

*File ini adalah referensi utama pengembangan Sistem Informasi UPTD BLKABK Balikpapan.*
*Selalu update file ini setiap ada perubahan scope, struktur, atau status iterasi.*
