# Diagram Sistem SI-LKABK
## ERD, Class Diagram, dan Sequence Diagram Per Iterasi

---

# ITERASI 1 — Autentikasi, Manajemen Akun, dan Pendaftaran Pasien

## ERD Iterasi 1

ERD pada iterasi pertama dirancang untuk mengakomodasi entitas-entitas yang
terlibat dalam proses autentikasi, manajemen akun, dan pendaftaran pasien.
Entitas `users` berfungsi sebagai tabel autentikasi pusat. Relasi antara `users`
dan `parent_guardians` bersifat *one-to-one*, sedangkan relasi antara
`parent_guardians` dan `patients` bersifat *one-to-many*.

```mermaid
erDiagram
    USERS {
        int id PK
        string name
        string email
        string password
        string role
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    PARENT_GUARDIANS {
        int id PK
        int user_id FK
        string name
        string relationship
        string phone
        string address
        datetime created_at
        datetime updated_at
    }

    PATIENTS {
        int id PK
        int guardian_id FK
        string medical_record_number
        string name
        date birth_date
        string birth_place
        string gender
        string address
        string diagnosis
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    THERAPY_TYPES {
        int id PK
        string name
        string description
        float price_per_session
        datetime created_at
        datetime updated_at
    }

    USERS         ||--o|  PARENT_GUARDIANS : "memiliki profil"
    PARENT_GUARDIANS ||--o{  PATIENTS        : "mendaftarkan"
    PATIENTS      }o--o{  THERAPY_TYPES    : "membutuhkan"
```

---

## Class Diagram Iterasi 1

*Class diagram* pada iterasi ini mendefinisikan struktur kelas dan hubungan
antarkelas yang diimplementasikan. Kelas `User` berperan sebagai entitas pusat
dengan atribut peran (*role*) yang menentukan hak akses. Kelas `ParentGuardian`
memiliki relasi *one-to-one* dengan `User` dan relasi *one-to-many* dengan
`Patient`.

```mermaid
classDiagram
    class User {
        +int id
        +String name
        +String email
        +String password
        +String role
        +Boolean is_active
        +login(email, password) bool
        +logout() void
        +changePassword(oldPass, newPass) bool
        +hasRole(role) bool
        +hasAnyRole(roles) bool
    }

    class ParentGuardian {
        +int id
        +int user_id
        +String name
        +String relationship
        +String phone
        +String address
        +getPatients() Collection
        +update(data) bool
    }

    class Patient {
        +int id
        +int guardian_id
        +String medical_record_number
        +String name
        +Date birth_date
        +String birth_place
        +String gender
        +String address
        +String diagnosis
        +Boolean is_active
        +register(data) Patient
        +getHistory() Collection
        +generateMRN() String
        +deactivate() bool
    }

    class TherapyType {
        +int id
        +String name
        +String description
        +Float price_per_session
        +getAll() Collection
        +findById(id) TherapyType
    }

    User        "1"   -->  "0..1"  ParentGuardian : has
    ParentGuardian "1" --> "0..*"  Patient        : registers
    Patient     "0..*" --> "0..*"  TherapyType   : needs
```

---

## Sequence Diagram Iterasi 1 — Login Sistem

Diagram ini menggambarkan alur interaksi antara pengguna dengan sistem pada saat
proses *login*, mulai dari pengisian formulir hingga pengarahan ke *dashboard*
sesuai peran. Terdapat tiga skenario alternatif: validasi gagal, *password* salah,
dan akun nonaktif.

```mermaid
sequenceDiagram
    actor Pengguna
    participant Vue  as Vue.js Frontend
    participant AC   as AuthController
    participant AS   as AuthService
    participant UM   as User Model
    participant DB   as PostgreSQL

    Pengguna ->> Vue  : Buka halaman /login
    Vue      ->> AC   : GET /login
    activate AC
    AC       -->> Vue : Render halaman login (Inertia)
    deactivate AC
    Vue      -->> Pengguna : Tampilkan form login

    Pengguna ->> Vue  : Isi email + password, klik "Login"
    Vue      ->> AC   : POST /login { email, password }
    activate AC

    AC ->> AC : validate(request)

    alt Validasi Gagal (kolom kosong / format salah)
        AC -->> Vue : Return 422 - error validasi
        Vue -->> Pengguna : Tampilkan pesan error validasi
    else Validasi Berhasil
        AC  ->> AS  : attempt(email, password)
        activate AS

        AS  ->> UM  : findByEmail(email)
        activate UM
        UM  ->> DB  : SELECT * FROM users WHERE email = ?
        activate DB
        DB  -->> UM : Return data user / null
        deactivate DB
        UM  -->> AS : Return User object / null
        deactivate UM

        alt User Tidak Ditemukan
            AS -->> AC : Authentication failed
            AC -->> Vue : Return error "Email atau password salah"
            Vue -->> Pengguna : Tampilkan pesan kesalahan
        else User Ditemukan
            AS ->> AS : verifyPassword(password, hash)

            alt Password Salah
                AS -->> AC : Authentication failed
                AC -->> Vue : Return error "Email atau password salah"
                Vue -->> Pengguna : Tampilkan pesan kesalahan
            else Password Benar
                AS ->> UM : checkIsActive(user)
                UM ->> DB : SELECT is_active FROM users WHERE id = ?
                DB -->> UM : Return is_active
                UM -->> AS : Return status aktif

                alt Akun Nonaktif
                    AS -->> AC : Account disabled
                    deactivate AS
                    AC -->> Vue : Return error "Akun Anda tidak aktif"
                    Vue -->> Pengguna : Tampilkan pesan akun nonaktif
                else Akun Aktif
                    AS ->> AS : createSession(user)
                    AS -->> AC : Return authenticated user
                    deactivate AS
                    AC ->> AC : redirectByRole(user.role)
                    AC -->> Vue : Redirect ke /dashboard
                    deactivate AC
                    Vue -->> Pengguna : Tampilkan dashboard sesuai role
                end
            end
        end
    end
```

---
---

# ITERASI 2 — Penjadwalan Terapi dan Pembayaran

## ERD Iterasi 2

ERD pada iterasi kedua diperluas dengan menambahkan entitas tenaga profesional
(`therapists`, `doctors`, `psychologists`) dan entitas operasional layanan
(`schedules`, `registrations`, `therapy_results`, `payments`). Entitas
`schedules` menjadi pusat relasi yang menghubungkan pasien dengan tenaga
profesional dan jenis terapi.

```mermaid
erDiagram
    PATIENTS {
        int id PK
        string name
        string medical_record_number
    }

    THERAPY_TYPES {
        int id PK
        string name
        float price_per_session
    }

    THERAPISTS {
        int id PK
        int user_id FK
        string name
        string specialization
        string phone
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    DOCTORS {
        int id PK
        int user_id FK
        string name
        string specialization
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    PSYCHOLOGISTS {
        int id PK
        int user_id FK
        string name
        string specialization
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    SCHEDULES {
        int id PK
        int patient_id FK
        int therapist_id FK
        int doctor_id FK
        int psychologist_id FK
        int therapy_type_id FK
        string professional_type
        date schedule_date
        time start_time
        time end_time
        string status
        string notes
        int created_by FK
        datetime created_at
        datetime updated_at
    }

    REGISTRATIONS {
        int id PK
        int patient_id FK
        int therapy_type_id FK
        int schedule_id FK
        date registration_date
        string status
        string notes
        datetime created_at
        datetime updated_at
    }

    THERAPY_RESULTS {
        int id PK
        int schedule_id FK
        string description
        string progress
        string next_session_plan
        datetime created_at
        datetime updated_at
    }

    PAYMENTS {
        int id PK
        int patient_id FK
        int registration_id FK
        float amount
        date payment_date
        string payment_method
        string status
        string notes
        datetime created_at
        datetime updated_at
    }

    PATIENTS         ||--o{  SCHEDULES        : "dijadwalkan dalam"
    THERAPISTS       ||--o{  SCHEDULES        : "menangani"
    DOCTORS          ||--o{  SCHEDULES        : "menangani"
    PSYCHOLOGISTS    ||--o{  SCHEDULES        : "menangani"
    THERAPY_TYPES    ||--o{  SCHEDULES        : "digunakan pada"
    SCHEDULES        ||--o|  THERAPY_RESULTS  : "menghasilkan"
    PATIENTS         ||--o{  REGISTRATIONS    : "membuat"
    THERAPY_TYPES    ||--o{  REGISTRATIONS    : "untuk jenis"
    SCHEDULES        ||--o|  REGISTRATIONS    : "terhubung ke"
    PATIENTS         ||--o{  PAYMENTS         : "membayar"
    REGISTRATIONS    ||--o|  PAYMENTS         : "menghasilkan"
```

---

## Class Diagram Iterasi 2

*Class diagram* pada iterasi 2 menambahkan kelas-kelas yang berkaitan dengan
penjadwalan dan pembayaran. Kelas `Schedule` memiliki metode `checkConflict()`
sebagai logika bisnis utama untuk mencegah bentrok jadwal antar pasien maupun
tenaga profesional.

```mermaid
classDiagram
    class Patient {
        +int id
        +String name
        +String medical_record_number
        +getSchedules() Collection
        +getPayments() Collection
        +getRegistrations() Collection
    }

    class Therapist {
        +int id
        +int user_id
        +String name
        +String specialization
        +String phone
        +Boolean is_active
        +getSchedules() Collection
        +getAvailableSlots(date) Collection
    }

    class Schedule {
        +int id
        +int patient_id
        +int therapist_id
        +int doctor_id
        +int psychologist_id
        +int therapy_type_id
        +String professional_type
        +Date schedule_date
        +Time start_time
        +Time end_time
        +String status
        +String notes
        +checkConflict(date, start, end, profId, type) bool
        +create(data) Schedule
        +cancel(reason) bool
        +markAsDone() bool
        +getByPatient(patientId) Collection
        +getByTherapist(therapistId) Collection
    }

    class Registration {
        +int id
        +int patient_id
        +int therapy_type_id
        +int schedule_id
        +Date registration_date
        +String status
        +String notes
        +approve() bool
        +reject(reason) bool
        +getByPatient(patientId) Collection
    }

    class TherapyResult {
        +int id
        +int schedule_id
        +String description
        +String progress
        +String next_session_plan
        +create(data) TherapyResult
        +update(data) bool
        +getBySchedule(scheduleId) TherapyResult
    }

    class Payment {
        +int id
        +int patient_id
        +int registration_id
        +Float amount
        +Date payment_date
        +String payment_method
        +String status
        +String notes
        +record(data) Payment
        +getByPatient(patientId) Collection
        +generateReceipt() File
    }

    Patient      "1"    -->  "0..*"   Schedule      : dijadwalkan
    Patient      "1"    -->  "0..*"   Registration  : membuat
    Patient      "1"    -->  "0..*"   Payment       : membayar
    Therapist    "1"    -->  "0..*"   Schedule      : menangani
    Schedule     "1"    -->  "0..1"   TherapyResult : menghasilkan
    Schedule     "1"    -->  "0..1"   Registration  : terhubung
    Registration "1"    -->  "0..1"   Payment       : menghasilkan
```

---

## Sequence Diagram Iterasi 2 — Buat Jadwal Terapi

Diagram ini menggambarkan alur pembuatan jadwal terapi oleh admin. Bagian kritis
dari alur ini adalah pengecekan konflik jadwal (`checkConflict`) sebelum data
jadwal disimpan ke basis data, untuk memastikan tidak ada bentrok waktu pada
terapis maupun pasien yang sama.

```mermaid
sequenceDiagram
    actor Admin
    participant Vue  as Vue.js Frontend
    participant SC   as ScheduleController
    participant SS   as ScheduleService
    participant SM   as Schedule Model
    participant DB   as PostgreSQL

    Admin ->> Vue : Buka halaman /schedules/create
    Vue   ->> SC  : GET /schedules/create
    activate SC
    SC    ->> DB  : SELECT patients, therapists, therapy_types
    DB    -->> SC : Return master data
    SC    -->> Vue : Render form jadwal (Inertia)
    deactivate SC
    Vue   -->> Admin : Tampilkan form pembuatan jadwal

    Admin ->> Vue : Pilih pasien, terapis, tanggal, jam mulai/selesai, jenis terapi
    Admin ->> Vue : Klik "Simpan Jadwal"
    Vue   ->> SC  : POST /schedules { patient_id, therapist_id, schedule_date, start_time, end_time, therapy_type_id }
    activate SC

    SC ->> SC : validate(request)

    alt Validasi Gagal (field kosong / format salah)
        SC -->> Vue : Return 422 - pesan validasi
        Vue -->> Admin : Tampilkan error pada field terkait
    else Validasi Berhasil
        SC  ->> SS  : createSchedule(validatedData)
        activate SS

        Note over SS, SM : Cek konflik jadwal terapis
        SS  ->> SM  : checkConflict(date, start, end, therapistId, "terapis")
        activate SM
        SM  ->> DB  : SELECT * FROM schedules WHERE therapist_id = ? AND schedule_date = ? AND status != "cancelled" AND (start_time < ? AND end_time > ?)
        activate DB
        DB  -->> SM : Return daftar jadwal yang konflik
        deactivate DB
        SM  -->> SS : Return konflik (ada / tidak)
        deactivate SM

        alt Ada Konflik Jadwal
            SS -->> SC : Throw ConflictException
            deactivate SS
            SC -->> Vue : Return 422 - "Terdapat konflik jadwal pada waktu tersebut"
            deactivate SC
            Vue -->> Admin : Tampilkan pesan konflik + slot yang bentrok
        else Tidak Ada Konflik
            SS  ->> SM  : create(scheduleData)
            activate SM
            SM  ->> DB  : INSERT INTO schedules VALUES (...)
            activate DB
            DB  -->> SM : Return schedule baru (id, data)
            deactivate DB
            SM  -->> SS : Return Schedule object
            deactivate SM
            SS  -->> SC : Return Schedule object
            deactivate SS
            SC  -->> Vue : Redirect /schedules + flash "Jadwal berhasil dibuat"
            deactivate SC
            Vue -->> Admin : Tampilkan daftar jadwal + notifikasi sukses
        end
    end
```

---
---

# ITERASI 3 — Rekam Medis Elektronik, Evaluasi Perkembangan, dan Laporan

## ERD Iterasi 3

ERD pada iterasi ketiga menambahkan entitas `medical_records` dan
`kpsp_screenings`. Entitas `medical_records` terhubung ke `patients` dan
`doctors`, sedangkan `kpsp_screenings` terhubung ke `patients` dan
`psychologists`. Keduanya bersifat *many-to-one* terhadap `patients`, artinya
satu pasien dapat memiliki banyak rekam medis dan banyak riwayat *screening*.

```mermaid
erDiagram
    PATIENTS {
        int id PK
        string name
        string medical_record_number
    }

    DOCTORS {
        int id PK
        int user_id FK
        string name
        string specialization
        string license_number
    }

    PSYCHOLOGISTS {
        int id PK
        int user_id FK
        string name
        string specialization
        string license_number
    }

    MEDICAL_RECORDS {
        int id PK
        int patient_id FK
        int doctor_id FK
        date visit_date
        string chief_complaint
        string physical_examination
        string diagnosis
        string icd_code
        string treatment_plan
        string notes
        datetime created_at
        datetime updated_at
    }

    KPSP_SCREENINGS {
        int id PK
        int patient_id FK
        int psychologist_id FK
        date screening_date
        int age_months
        int total_score
        string result
        string notes
        string recommendations
        datetime created_at
        datetime updated_at
    }

    PATIENTS      ||--o{  MEDICAL_RECORDS  : "memiliki"
    DOCTORS       ||--o{  MEDICAL_RECORDS  : "membuat"
    PATIENTS      ||--o{  KPSP_SCREENINGS  : "menjalani"
    PSYCHOLOGISTS ||--o{  KPSP_SCREENINGS  : "melakukan"
```

---

## Class Diagram Iterasi 3

*Class diagram* pada iterasi 3 menambahkan kelas `MedicalRecord`, `KpspScreening`,
dan `ReportService`. Kelas `KpspScreening` memiliki metode bisnis penting yaitu
`calculateScore()` untuk menghitung skor secara otomatis dan `determineResult()`
untuk mengkategorikan hasil *screening* berdasarkan skor dan usia anak. Kelas
`ReportService` bersifat *stateless* dan digunakan untuk menghasilkan laporan dari
berbagai modul.

```mermaid
classDiagram
    class Doctor {
        +int id
        +int user_id
        +String name
        +String specialization
        +String license_number
        +getMedicalRecords() Collection
        +createMedicalRecord(data) MedicalRecord
    }

    class Psychologist {
        +int id
        +int user_id
        +String name
        +String specialization
        +String license_number
        +getScreenings() Collection
        +createScreening(data) KpspScreening
    }

    class Patient {
        +int id
        +String name
        +String medical_record_number
        +getMedicalRecords() Collection
        +getScreenings() Collection
        +getServiceHistory() Collection
    }

    class MedicalRecord {
        +int id
        +int patient_id
        +int doctor_id
        +Date visit_date
        +String chief_complaint
        +String physical_examination
        +String diagnosis
        +String icd_code
        +String treatment_plan
        +String notes
        +create(data) MedicalRecord
        +update(data) bool
        +getByPatient(patientId) Collection
        +getLatest(patientId) MedicalRecord
    }

    class KpspScreening {
        +int id
        +int patient_id
        +int psychologist_id
        +Date screening_date
        +int age_months
        +int total_score
        +String result
        +String notes
        +String recommendations
        +calculateScore(answers) int
        +determineResult(score, ageMonths) String
        +generateRecommendation(result) String
        +create(data) KpspScreening
        +getByPatient(patientId) Collection
    }

    class ReportService {
        +generateMonthlyReport(month, year) Array
        +generateByTherapist(therapistId, period) Array
        +generateByTherapyType(typeId, period) Array
        +getPatientStatistics(period) Array
        +exportToPDF(data) File
        +exportToExcel(data) File
    }

    Doctor       "1"    -->   "0..*"  MedicalRecord  : creates
    Psychologist "1"    -->   "0..*"  KpspScreening  : conducts
    Patient      "1"    -->   "0..*"  MedicalRecord  : has
    Patient      "1"    -->   "0..*"  KpspScreening  : undergoes
    ReportService        "1"  ..>     MedicalRecord  : uses
    ReportService        "1"  ..>     KpspScreening  : uses
```

---

## Sequence Diagram Iterasi 3 — Input Rekam Medis Elektronik

Diagram ini menggambarkan alur dokter dalam mengisi rekam medis elektronik pasien.
Alur mencakup pemuatan data riwayat pasien sebelumnya untuk mendukung keputusan
klinis, pengisian formulir rekam medis, validasi dan otorisasi, hingga penyimpanan
data ke basis data.

```mermaid
sequenceDiagram
    actor Dokter
    participant Vue  as Vue.js Frontend
    participant MRC  as MedicalRecordController
    participant MRS  as MedicalRecordService
    participant MRM  as MedicalRecord Model
    participant PM   as Patient Model
    participant DB   as PostgreSQL

    Dokter ->> Vue  : Buka halaman /doctor/medical-records/create
    Vue    ->> MRC  : GET /doctor/medical-records/create
    activate MRC
    MRC    ->> DB   : SELECT patients (is_active = true)
    DB     -->> MRC : Return daftar pasien aktif
    MRC    -->> Vue : Render form rekam medis (Inertia)
    deactivate MRC
    Vue    -->> Dokter : Tampilkan form rekam medis + dropdown pasien

    Dokter ->> Vue  : Pilih pasien dari dropdown
    Vue    ->> MRC  : GET /patients/{id}/summary
    activate MRC
    MRC    ->> PM   : findWithLatestRecord(patientId)
    activate PM
    PM     ->> DB   : SELECT patient + medical_records ORDER BY visit_date DESC LIMIT 5
    DB     -->> PM  : Return data pasien + 5 rekam medis terakhir
    PM     -->> MRC : Return Patient + history
    deactivate PM
    MRC    -->> Vue : Return JSON { patient, medical_history }
    deactivate MRC
    Vue    -->> Dokter : Tampilkan info pasien + riwayat rekam medis sebelumnya

    Note over Dokter, Vue : Dokter mengisi keluhan, pemeriksaan fisik,\ndiagnosis (ICD-10), dan rencana terapi

    Dokter ->> Vue  : Klik "Simpan Rekam Medis"
    Vue    ->> MRC  : POST /doctor/medical-records { patient_id, visit_date, chief_complaint, physical_examination, diagnosis, icd_code, treatment_plan, notes }
    activate MRC

    MRC ->> MRC : validate(request)

    alt Validasi Gagal (field wajib kosong)
        MRC -->> Vue : Return 422 - error validasi per field
        Vue -->> Dokter : Tampilkan pesan error pada field terkait
    else Validasi Berhasil
        MRC ->> MRC : authorize(dokter, "create", MedicalRecord)

        alt Otorisasi Gagal (bukan role dokter)
            MRC -->> Vue : Return 403 - Forbidden
            Vue -->> Dokter : Tampilkan pesan tidak memiliki akses
        else Otorisasi Berhasil
            MRC  ->> MRS  : createRecord(data, doctorId)
            activate MRS

            MRS  ->> MRM  : create(recordData)
            activate MRM
            MRM  ->> DB   : INSERT INTO medical_records VALUES (patient_id, doctor_id, visit_date, ...)
            activate DB
            DB   -->> MRM : Return new record { id, ... }
            deactivate DB
            MRM  -->> MRS : Return MedicalRecord object
            deactivate MRM

            MRS  -->> MRC : Return MedicalRecord object
            deactivate MRS

            MRC  -->> Vue : Redirect /doctor/medical-records/{id} + flash "Rekam medis berhasil disimpan"
            deactivate MRC
            Vue  -->> Dokter : Tampilkan detail rekam medis + notifikasi berhasil
        end
    end
```
