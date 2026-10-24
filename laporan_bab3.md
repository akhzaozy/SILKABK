# BAB 3
# DESAIN DAN IMPLEMENTASI

Bab ini menjelaskan metodologi penelitian yang digunakan sebagai landasan dalam
pelaksanaan dan pengembangan sistem pada penelitian ini. Pembahasan mencakup
pendekatan penelitian, model pengembangan sistem yang diterapkan, serta
tahapan-tahapan kerja yang dilakukan secara sistematis mulai dari analisis
kebutuhan, perancangan, implementasi, pengujian, hingga tahap pemeliharaan
sistem.

---

## 3.1 Konsep Desain

### 3.1.1 Gambaran Umum Sistem Informasi Layanan Kesehatan Anak Berkebutuhan Khusus

Sistem Informasi Layanan Kesehatan Anak Berkebutuhan Khusus (SI-LKABK)
merupakan aplikasi berbasis *website* yang dikembangkan untuk mendukung
pengelolaan operasional layanan kesehatan di UPTD Balai Layanan Kesehatan Anak
Berkebutuhan Khusus (BLKABK) Kota Balikpapan. Sistem ini dirancang untuk
menggantikan proses manual yang selama ini menggunakan formulir kertas, Google
Spreadsheet, dan komunikasi melalui WhatsApp yang tidak terintegrasi, sehingga
seluruh alur kerja layanan dapat dikelola secara terpusat, aman, dan efisien.

Secara arsitektural, SI-LKABK dikembangkan menggunakan pendekatan *modern
monolith* dengan *stack* teknologi yang terdiri dari Laravel sebagai *backend
framework*, Inertia.js sebagai *server-client adapter*, Vue.js sebagai *frontend
framework*, dan PostgreSQL sebagai sistem manajemen basis data. Pendekatan ini
dipilih karena memungkinkan pengembangan oleh satu orang (*solo developer*)
secara efisien tanpa mengorbankan kualitas antarmuka pengguna yang modern dan
responsif.

Sistem mencakup lima modul fungsional utama sebagai berikut:

1. **Modul Pendaftaran Pasien**: Mengelola proses pendaftaran pasien baru maupun
   lama, baik oleh admin secara langsung maupun oleh orang tua secara mandiri
   melalui sistem.
2. **Modul Rekam Medis Elektronik**: Menyimpan dan menampilkan riwayat
   kesehatan, hasil pemeriksaan dokter, catatan psikolog, serta riwayat terapi
   pasien sesuai standar Peraturan Menteri Kesehatan Nomor 24 Tahun 2022
   tentang Rekam Medis Elektronik.
3. **Modul Penjadwalan Terapi**: Mengatur jadwal sesi terapi dengan mekanisme
   validasi otomatis untuk mencegah konflik jadwal antar pasien maupun tenaga
   profesional, menggantikan sistem Google Spreadsheet yang rentan terhadap
   penghapusan data tidak sengaja.
4. **Modul Pembayaran**: Mencatat transaksi pembayaran layanan dan menyediakan
   riwayat serta bukti pembayaran yang dapat diakses oleh admin maupun orang
   tua pasien.
5. **Modul Pelaporan dan *Screening* Perkembangan Anak**: Menghasilkan laporan
   layanan kesehatan secara terintegrasi dan mendukung pelaksanaan *screening*
   perkembangan anak (KPSP) oleh psikolog secara digital.

Setiap modul dirancang dengan mekanisme kontrol akses berbasis peran
(*Role-Based Access Control*/RBAC) yang memastikan setiap pengguna hanya dapat
mengakses fitur yang sesuai dengan perannya. Sistem ini juga mendukung
penyusunan laporan bulanan dan tahunan secara otomatis untuk mendukung
kepatuhan UPTD BLKABK terhadap standar pelaporan layanan kesehatan yang
ditetapkan oleh Kementerian Kesehatan Republik Indonesia.

[Gambar 3.2 Gambaran Umum Arsitektur Sistem SI-LKABK]

### 3.1.2 Aktor dan Peran

Sistem informasi ini mendukung enam peran pengguna dengan hak akses yang
berbeda-beda sesuai dengan fungsi dan tanggung jawab masing-masing dalam
operasional layanan UPTD BLKABK. Definisi lengkap setiap aktor beserta tugas
utamanya disajikan pada Tabel 3.1 berikut.

**Tabel 3.1 Definisi Aktor Sistem SI-LKABK**

| No | Aktor       | Definisi                                                                                                                                                                                                   |
|:--:|-------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1  | Super Admin | Bertanggung jawab mengelola seluruh akun pengguna dalam sistem, termasuk pembuatan, pengubahan, dan penonaktifan akun. Super admin memiliki akses penuh terhadap seluruh fitur dan data dalam sistem.       |
| 2  | Admin       | Bertanggung jawab mengelola operasional harian layanan, mencakup pendaftaran pasien, pengelolaan data pasien, pencatatan pembayaran, penjadwalan terapi, dan pembuatan laporan layanan kesehatan.           |
| 3  | Dokter      | Bertanggung jawab melakukan pemeriksaan medis dan pencatatan rekam medis pasien secara elektronik, termasuk anamnesis, diagnosis menggunakan kode ICD-10, rencana penanganan, dan hasil pemeriksaan fisik.  |
| 4  | Terapis     | Bertanggung jawab melaksanakan sesi terapi sesuai jadwal yang telah ditetapkan dan mencatat hasil serta perkembangan terapi pasien setelah setiap sesi berlangsung.                                        |
| 5  | Psikolog    | Bertanggung jawab melakukan evaluasi psikologis, *screening* perkembangan anak menggunakan instrumen KPSP, dan pencatatan hasil asesmen psikologi secara digital.                                          |
| 6  | Orang Tua   | Bertanggung jawab mendaftarkan anak untuk mendapatkan layanan, memilih jadwal terapi, memantau perkembangan layanan, dan mengakses bukti pembayaran secara mandiri melalui sistem.                         |

---

## 3.2 Spesifikasi Teknis

Spesifikasi teknis menjelaskan kebutuhan minimum lingkungan pengembangan dan
pengujian sistem, serta spesifikasi perangkat yang dibutuhkan agar aplikasi
SI-LKABK dapat berjalan dengan optimal.

### 3.2.1 Spesifikasi Perangkat Lunak

Spesifikasi perangkat lunak mencakup seluruh komponen *software* yang digunakan
dalam proses pengembangan dan pengoperasian sistem. Pemilihan setiap komponen
didasarkan pada kebutuhan fungsional sistem, kompatibilitas antar teknologi, serta
kemampuan mendukung pengembangan oleh *solo developer* secara efisien. Tabel
3.2 menyajikan rincian spesifikasi perangkat lunak yang digunakan dalam
pengembangan SI-LKABK.

**Tabel 3.2 Spesifikasi Perangkat Lunak**

| No | Komponen                      | Spesifikasi              | Definisi                                                                                                                                    |
|:--:|-------------------------------|--------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| 1  | Sistem Operasi                | Windows 11               | Digunakan sebagai sistem operasi utama pada lingkungan pengembangan lokal.                                                                  |
| 2  | *Web Server*                  | Apache 2.4 (via XAMPP)   | Menyajikan aplikasi web SI-LKABK selama proses pengembangan dan pengujian lokal.                                                           |
| 3  | Bahasa Pemrograman *Backend*  | PHP 8.2                  | Bahasa pemrograman utama untuk pengembangan sisi *backend* menggunakan *framework* Laravel.                                                 |
| 4  | Bahasa Pemrograman *Frontend* | JavaScript               | Digunakan bersama Vue.js 3 untuk membangun antarmuka pengguna yang dinamis dan interaktif.                                                  |
| 5  | *Framework Backend*           | Laravel 12.x             | *Framework* PHP berbasis arsitektur MVC yang menangani logika bisnis, *routing*, autentikasi, dan manajemen basis data melalui Eloquent ORM. |
| 6  | *Framework Frontend*          | Vue.js 3                 | *Framework* JavaScript progresif untuk membangun antarmuka pengguna berbasis komponen yang reaktif dan responsif.                           |
| 7  | *Server-Client Adapter*       | Inertia.js               | Menghubungkan *backend* Laravel dengan *frontend* Vue.js sehingga aplikasi berperilaku seperti SPA tanpa memerlukan REST API terpisah.      |
| 8  | Basis Data                    | PostgreSQL 15            | Sistem manajemen basis data relasional (*ORDBMS*) yang menyimpan seluruh data layanan kesehatan secara terpusat, aman, dan mendukung ACID.  |
| 9  | *CSS Framework*               | Tailwind CSS 3           | *Framework* CSS *utility-first* yang digunakan untuk membangun antarmuka yang responsif dan konsisten di berbagai ukuran layar.             |
| 10 | *Build Tool*                  | Vite                     | Alat *build* modern untuk mengompilasi dan menggabungkan aset *frontend* secara efisien pada mode pengembangan maupun produksi.             |
| 11 | *Version Control*             | Git & GitHub             | Mengelola versi kode sumber dan repositori pengembangan sistem.                                                                             |
| 12 | *Editor*/IDE                  | Visual Studio Code       | Lingkungan penulisan kode sumber utama yang dilengkapi ekstensi pendukung PHP, Vue.js, dan Laravel.                                        |
| 13 | *Browser* Pengujian           | Google Chrome, Firefox   | Digunakan untuk menguji tampilan dan fungsionalitas sistem dari sisi klien.                                                                 |

### 3.2.2 Spesifikasi Perangkat Keras

Spesifikasi perangkat keras mencakup kebutuhan minimum perangkat yang digunakan
selama proses pengembangan maupun oleh pengguna akhir dalam mengakses sistem
SI-LKABK. Tabel 3.3 menyajikan rincian spesifikasi perangkat keras yang
digunakan.

**Tabel 3.3 Spesifikasi Perangkat Keras**

| No | Perangkat               | Spesifikasi                                                               | Keterangan                                                                             |
|:--:|-------------------------|---------------------------------------------------------------------------|----------------------------------------------------------------------------------------|
| 1  | Laptop Pengembang       | Prosesor AMD Ryzen 5, RAM 16 GB, SSD 512 GB                              | Digunakan sebagai lingkungan utama pengembangan dan pengujian lokal sistem.            |
| 2  | Jaringan                | Koneksi internet stabil ≥ 10 Mbps                                        | Mendukung proses pengembangan, pengujian, dan akses repositori GitHub.                 |
| 3  | Perangkat Klien Minimum | Prosesor *dual-core*, RAM 4 GB, *browser* modern (Chrome/Firefox/Safari) | Spesifikasi minimum perangkat pengguna akhir untuk mengakses sistem melalui *browser*. |
| 4  | Perangkat *Mobile*      | *Smartphone* RAM ≥ 2 GB, *browser mobile* modern                         | Memastikan tampilan sistem dapat diakses dengan baik menggunakan desain responsif.     |

---

## 3.4 Metode Implementasi

Metode implementasi pada penelitian ini mengikuti siklus metode *Personal Extreme
Programming* (PXP) yang terdiri dari tahapan *design*, *unit testing*, *code*,
*refactoring*, *system testing*, dan *retrospective*, yang diulang secara iteratif
untuk setiap iterasi pengembangan. Implementasi prototipe SI-LKABK dilakukan
menggunakan Laravel 12.x pada sisi *backend* serta Vue.js 3 dan Inertia.js pada
sisi *frontend*, dengan mengacu pada rancangan desain yang disusun pada setiap
iterasi. Berdasarkan *user story* yang telah diidentifikasi dan prioritas MoSCoW
yang telah ditetapkan pada tahap *planning*, pengembangan dibagi menjadi tiga
iterasi utama.

---

### 3.4.1 Iterasi 1

Iterasi 1 berfokus pada implementasi fitur fondasi sistem yang mencakup
autentikasi pengguna, manajemen akun oleh *super admin*, serta proses pendaftaran
dan pengelolaan data pasien. Penentuan prioritas *user story* pada iterasi pertama
dilakukan menggunakan metode MoSCoW, dengan rincian yang dapat dilihat pada
Tabel 3.4 berikut.

**Tabel 3.4 *User Story* Iterasi 1**

| *User Story* | Judul                  | Aktor           | Prioritas MoSCoW | Task                                                         |
|:------------:|------------------------|-----------------|:----------------:|--------------------------------------------------------------|
| US-01        | Login Sistem           | Semua Pengguna  | *Must Have*      | Pembuatan halaman *login*                                    |
| US-02        | *Logout* Sistem        | Semua Pengguna  | *Must Have*      | Implementasi *logout* dan invalidasi sesi                    |
| US-03        | Kelola Akun Pengguna   | *Super Admin*   | *Must Have*      | Pembuatan CRUD akun pengguna dan manajemen peran             |
| US-04        | Pendaftaran Pasien     | Admin           | *Must Have*      | Pembuatan halaman pendaftaran pasien baru oleh admin         |
| US-05        | Pendaftaran Layanan    | Orang Tua       | *Should Have*    | Pembuatan halaman pendaftaran mandiri oleh orang tua         |
| US-06        | Kelola Data Pasien     | Admin           | *Must Have*      | Pembuatan CRUD data pasien dengan pencarian dan filter       |

#### 3.4.1.1 *Design* 1

Tahap *design* pada iterasi pertama bertujuan untuk mentransformasikan *user
story* yang telah ditetapkan ke dalam *blueprint* teknis sistem. Rancangan pada
iterasi ini mencakup *use case diagram*, *Entity Relationship Diagram* (ERD),
*class diagram*, dan perancangan antarmuka pengguna (*wireframe*).

**A. *Use Case Diagram* Iterasi 1**

*Use case diagram* pada iterasi ini menggambarkan interaksi seluruh aktor dengan
fitur-fitur utama yang dikembangkan pada iterasi 1. Terdapat 6 *use case* yang
didefinisikan, mencakup proses *login* dan *logout* yang dapat dilakukan oleh
seluruh pengguna, pengelolaan akun oleh *super admin*, serta pendaftaran dan
pengelolaan data pasien oleh admin dan orang tua. Diagram ini memastikan bahwa
batasan hak akses antara *super admin*, admin, dan orang tua terdefinisi dengan
jelas.

[Gambar 3.3 *Use Case Diagram* Iterasi 1]

**B. *Entity Relationship Diagram* Iterasi 1**

ERD pada iterasi pertama dirancang untuk mengakomodasi entitas-entitas yang
terlibat dalam proses autentikasi, manajemen akun, dan pendaftaran pasien.
Entitas utama yang terlibat meliputi `users`, `parent_guardians`, `patients`, dan
`therapy_types`. Entitas `users` berfungsi sebagai tabel autentikasi pusat yang
menyimpan atribut kredensial dan peran pengguna. Relasi antara `users` dan
`parent_guardians` bersifat *one-to-one*, sedangkan relasi antara
`parent_guardians` dan `patients` bersifat *one-to-many*.

[Gambar 3.4 ERD Iterasi 1]

**Tabel 3.5 Kamus Data dan Fungsi Iterasi 1**

| No | Entitas          | Atribut                 | Tipe Data                                                                    |
|:--:|------------------|-------------------------|------------------------------------------------------------------------------|
| 1  | users            | id                      | bigInt                                                                       |
|    |                  | name                    | varchar(100)                                                                 |
|    |                  | email                   | varchar(100)                                                                 |
|    |                  | password                | varchar                                                                      |
|    |                  | role                    | enum('super_admin','admin','dokter','terapis','psikolog','orang_tua')        |
|    |                  | is_active               | boolean                                                                      |
|    |                  | created_at              | timestamp                                                                    |
|    |                  | updated_at              | timestamp                                                                    |
| 2  | parent_guardians | id                      | bigInt                                                                       |
|    |                  | user_id                 | bigInt                                                                       |
|    |                  | name                    | varchar(100)                                                                 |
|    |                  | relationship            | varchar(50)                                                                  |
|    |                  | phone                   | varchar(20)                                                                  |
|    |                  | address                 | text                                                                         |
|    |                  | created_at              | timestamp                                                                    |
|    |                  | updated_at              | timestamp                                                                    |
| 3  | patients         | id                      | bigInt                                                                       |
|    |                  | guardian_id             | bigInt                                                                       |
|    |                  | medical_record_number   | varchar(20)                                                                  |
|    |                  | name                    | varchar(100)                                                                 |
|    |                  | birth_date              | date                                                                         |
|    |                  | birth_place             | varchar(100)                                                                 |
|    |                  | gender                  | enum('L','P')                                                                |
|    |                  | address                 | text                                                                         |
|    |                  | diagnosis               | text                                                                         |
|    |                  | is_active               | boolean                                                                      |
|    |                  | created_at              | timestamp                                                                    |
|    |                  | updated_at              | timestamp                                                                    |
| 4  | therapy_types    | id                      | bigInt                                                                       |
|    |                  | name                    | varchar(100)                                                                 |
|    |                  | description             | text                                                                         |
|    |                  | price_per_session       | decimal(10,2)                                                                |
|    |                  | created_at              | timestamp                                                                    |
|    |                  | updated_at              | timestamp                                                                    |

**C. *Class Diagram* Iterasi 1**

*Class diagram* pada iterasi ini mendefinisikan struktur kelas dan hubungan
antarkelas yang diimplementasikan dalam sistem. Kelas `User` berperan sebagai
entitas pusat yang memiliki relasi dengan kelas `ParentGuardian` dan `Patient`.
Metode-metode yang tersedia pada setiap kelas dirancang sesuai dengan operasi
CRUD yang dibutuhkan.

[Gambar 3.5 *Class Diagram* Iterasi 1]

**D. *User Interface* Iterasi 1**

[Gambar 3.6 *Wireframe* Halaman *Login*]

[Gambar 3.7 *Wireframe* Halaman *Dashboard* Admin]

[Gambar 3.8 *Wireframe* Halaman Pendaftaran Pasien]

[Gambar 3.9 *Wireframe* Halaman Kelola Data Pasien]

#### 3.4.1.2 *Unit Testing* 1

Pengujian unit pada iterasi 1 dilakukan menggunakan metode *Black Box Testing*
untuk memastikan setiap fungsi yang dikembangkan berjalan sesuai dengan
*acceptance criteria* yang telah ditetapkan. Pengujian dilakukan oleh pengembang
dengan menguji skenario valid (*positive case*) dan skenario tidak valid (*negative
case*) pada setiap fitur.

**Tabel 3.6 Hasil *Unit Testing* Iterasi 1**

| *Test Case*                            | *Test Scenario*                                              | *Expected Result*                                                  | *Result* |
|----------------------------------------|--------------------------------------------------------------|--------------------------------------------------------------------|:--------:|
| Login — data valid                     | Memasukkan email dan *password* yang terdaftar               | Sistem mengarahkan ke *dashboard* sesuai peran pengguna            | Pass     |
| Login — *password* salah              | Memasukkan email valid dan *password* salah                  | Sistem menampilkan pesan "Email atau *password* salah"             | Pass     |
| Login — email tidak terdaftar          | Memasukkan email yang tidak ada di sistem                    | Sistem menampilkan pesan kesalahan                                 | Pass     |
| *Logout*                               | Menekan tombol keluar dari *dashboard*                       | Sesi diakhiri dan pengguna diarahkan ke halaman *login*            | Pass     |
| Tambah akun — data lengkap             | *Super admin* mengisi semua kolom dengan benar               | Akun berhasil dibuat dan muncul di daftar pengguna                 | Pass     |
| Tambah akun — email duplikat           | *Super admin* memasukkan email yang sudah terdaftar          | Sistem menampilkan pesan "Email sudah digunakan"                   | Pass     |
| Pendaftaran pasien — data lengkap      | Admin mengisi semua kolom pendaftaran pasien                 | Pasien berhasil terdaftar dengan nomor rekam medis otomatis        | Pass     |
| Pendaftaran pasien — kolom wajib kosong| Admin mengirim formulir dengan kolom nama kosong             | Sistem menampilkan pesan validasi pada kolom yang kosong           | Pass     |
| Kelola data pasien — edit data         | Admin mengubah alamat pasien dan menyimpan                   | Data pasien berhasil diperbarui di sistem                          | Pass     |
| Kelola data pasien — nonaktifkan       | Admin menonaktifkan akun pasien                              | Status pasien berubah menjadi tidak aktif                          | Pass     |

#### 3.4.1.3 *Code* 1

[Screenshot Halaman *Login*]

[Screenshot Halaman *Dashboard* Admin]

[Screenshot Halaman Manajemen Akun Pengguna (*Super Admin*)]

[Screenshot Halaman Pendaftaran Pasien]

[Screenshot Halaman Daftar Pasien]

#### 3.4.1.4 *Refactoring* 1

Proses *refactoring* pada iterasi 1 dilakukan untuk meningkatkan kualitas dan
keterbacaan kode tanpa mengubah fungsionalitas yang sudah berjalan. Kegiatan
*refactoring* yang dilakukan meliputi:

1. Ekstraksi logika bisnis ke dalam *Service Class* agar *controller* tetap ramping
   dan sesuai prinsip *Single Responsibility*.
2. Penerapan *Form Request* untuk validasi *input* yang lebih terstruktur.
3. Penyeragaman penamaan variabel, metode, dan komponen menggunakan konvensi
   Laravel dan Vue.js.
4. Pembuatan *reusable components* Vue.js untuk elemen antarmuka yang berulang
   seperti tabel, *form input*, dan tombol aksi.

#### 3.4.1.5 *System Testing* 1

*System testing* pada iterasi 1 dilakukan secara menyeluruh untuk menguji
integrasi antara modul autentikasi, manajemen akun, dan pendaftaran pasien
menggunakan metode *Black Box Testing*.

**Tabel 3.7 Hasil *System Testing* Iterasi 1**

| *Test Case*          | *Test Scenario*                                              | *Expected Result*                                             | *Tester*    | *Result* |
|----------------------|--------------------------------------------------------------|---------------------------------------------------------------|-------------|:--------:|
| Login                | Akses halaman *login*, isi email dan *password*, klik *login* | Pengguna berhasil masuk ke *dashboard* sesuai peran           | Admin       | Pass     |
| *Logout*             | Klik tombol keluar dari *dashboard*                          | Pengguna kembali ke halaman *login*                           | Admin       | Pass     |
| Kelola Akun          | *Super admin* membuat akun baru peran terapis                | Akun terapis berhasil dibuat dan dapat *login*                | Super Admin | Pass     |
| Pendaftaran Pasien   | Admin mendaftarkan pasien baru dengan data lengkap           | Pasien terdaftar dengan nomor rekam medis otomatis            | Admin       | Pass     |
| Pendaftaran Mandiri  | Orang tua mendaftarkan anak melalui sistem                   | Data pendaftaran tersimpan dan terlihat oleh admin            | Orang Tua   | Pass     |
| Kelola Data Pasien   | Admin memperbarui data alamat pasien                         | Perubahan tersimpan dan tampil di halaman detail pasien       | Admin       | Pass     |

#### 3.4.1.6 *Retrospective* 1

Pada tahap *retrospective* iterasi 1, dilakukan evaluasi menyeluruh terhadap
proses dan hasil pengembangan. Seluruh fitur yang ditargetkan pada iterasi ini
berhasil diimplementasikan sesuai dengan *acceptance criteria* yang telah
ditetapkan. Masukan yang diperoleh dari pengujian mencakup penyesuaian tampilan
formulir pendaftaran agar lebih intuitif, serta penambahan konfirmasi sebelum
proses penghapusan data. Catatan perbaikan tersebut akan diterapkan pada iterasi
berikutnya.

---

### 3.4.2 Iterasi 2

Iterasi 2 berfokus pada implementasi fitur penjadwalan terapi yang terintegrasi
dengan data pasien dan tenaga profesional, serta fitur pencatatan dan pemantauan
pembayaran layanan. Penentuan prioritas *user story* pada iterasi kedua dilakukan
menggunakan metode MoSCoW, dengan rincian yang dapat dilihat pada Tabel 3.8
berikut.

**Tabel 3.8 *User Story* Iterasi 2**

| *User Story* | Judul                  | Aktor         | Prioritas MoSCoW | Task                                                              |
|:------------:|------------------------|---------------|:----------------:|-------------------------------------------------------------------|
| US-07        | Pencatatan Pembayaran  | Admin         | *Must Have*      | Pembuatan modul pencatatan pembayaran layanan                     |
| US-08        | Riwayat Pembayaran     | Admin         | *Should Have*    | Pembuatan halaman riwayat dan filter pembayaran                   |
| US-09        | Lihat Bukti Pembayaran | Orang Tua     | *Could Have*     | Pembuatan halaman bukti pembayaran untuk orang tua                |
| US-10        | Kelola Jadwal Terapi   | Admin         | *Must Have*      | Pembuatan sistem penjadwalan dengan validasi konflik jadwal       |
| US-11        | Lihat Jadwal Terapi    | Orang Tua     | *Must Have*      | Pembuatan halaman jadwal terapi anak                              |
| US-12        | Lihat Jadwal Terapis   | Terapis       | *Must Have*      | Pembuatan halaman jadwal personal terapis                         |
| US-13        | Catat Hasil Terapi     | Terapis       | *Must Have*      | Pembuatan *form* pencatatan hasil dan perkembangan terapi         |

#### 3.4.2.1 *Design* 2

Tahap *design* pada iterasi kedua berfokus pada perancangan modul penjadwalan
terapi dan pembayaran yang terintegrasi dengan data pasien dan tenaga profesional
dari iterasi sebelumnya.

**A. *Use Case Diagram* Iterasi 2**

*Use case diagram* pada iterasi ini menggambarkan interaksi aktor admin, terapis,
dan orang tua dengan fitur penjadwalan dan pembayaran. Admin memiliki hak untuk
membuat dan mengelola jadwal terapi serta mencatat pembayaran. Terapis dapat
melihat jadwal dan mencatat hasil terapi. Orang tua dapat melihat jadwal terapi
anak serta mengakses bukti pembayaran secara mandiri.

[Gambar 3.10 *Use Case Diagram* Iterasi 2]

**B. *Entity Relationship Diagram* Iterasi 2**

ERD pada iterasi kedua diperluas dengan menambahkan entitas `therapists`,
`schedules`, `registrations`, `therapy_results`, dan `payments`. Entitas
`schedules` menjadi penghubung antara pasien dan tenaga profesional dengan
menyimpan informasi tanggal, waktu, dan status sesi terapi. Relasi antara
`schedules` dan `therapy_results` bersifat *one-to-one*, sedangkan relasi antara
`patients` dan `payments` bersifat *one-to-many*.

[Gambar 3.11 ERD Iterasi 2]

**Tabel 3.9 Kamus Data dan Fungsi Iterasi 2**

| No | Entitas          | Atribut            | Tipe Data                                                          |
|:--:|------------------|--------------------|---------------------------------------------------------------------|
| 1  | therapists       | id                 | bigInt                                                              |
|    |                  | user_id            | bigInt                                                              |
|    |                  | name               | varchar(100)                                                        |
|    |                  | specialization     | varchar(100)                                                        |
|    |                  | phone              | varchar(20)                                                         |
|    |                  | is_active          | boolean                                                             |
|    |                  | created_at         | timestamp                                                           |
|    |                  | updated_at         | timestamp                                                           |
| 2  | schedules        | id                 | bigInt                                                              |
|    |                  | patient_id         | bigInt                                                              |
|    |                  | therapist_id       | bigInt (nullable)                                                   |
|    |                  | doctor_id          | bigInt (nullable)                                                   |
|    |                  | psychologist_id    | bigInt (nullable)                                                   |
|    |                  | professional_type  | enum('terapis','dokter','psikolog')                                 |
|    |                  | therapy_type_id    | bigInt                                                              |
|    |                  | schedule_date      | date                                                                |
|    |                  | start_time         | time                                                                |
|    |                  | end_time           | time                                                                |
|    |                  | status             | enum('available','scheduled','done','cancelled')                    |
|    |                  | notes              | text                                                                |
|    |                  | created_by         | bigInt                                                              |
|    |                  | created_at         | timestamp                                                           |
|    |                  | updated_at         | timestamp                                                           |
| 3  | registrations    | id                 | bigInt                                                              |
|    |                  | patient_id         | bigInt                                                              |
|    |                  | therapy_type_id    | bigInt                                                              |
|    |                  | schedule_id        | bigInt                                                              |
|    |                  | registration_date  | date                                                                |
|    |                  | status             | enum('pending','approved','rejected')                               |
|    |                  | notes              | text                                                                |
|    |                  | created_at         | timestamp                                                           |
|    |                  | updated_at         | timestamp                                                           |
| 4  | therapy_results  | id                 | bigInt                                                              |
|    |                  | schedule_id        | bigInt                                                              |
|    |                  | description        | text                                                                |
|    |                  | progress           | text                                                                |
|    |                  | next_session_plan  | text                                                                |
|    |                  | created_at         | timestamp                                                           |
|    |                  | updated_at         | timestamp                                                           |
| 5  | payments         | id                 | bigInt                                                              |
|    |                  | patient_id         | bigInt                                                              |
|    |                  | registration_id    | bigInt                                                              |
|    |                  | amount             | decimal(10,2)                                                       |
|    |                  | payment_date       | date                                                                |
|    |                  | payment_method     | varchar(50)                                                         |
|    |                  | status             | enum('paid','unpaid','cancelled')                                   |
|    |                  | notes              | text                                                                |
|    |                  | created_at         | timestamp                                                           |
|    |                  | updated_at         | timestamp                                                           |

**C. *Class Diagram* Iterasi 2**

*Class diagram* pada iterasi 2 menambahkan kelas-kelas baru yang berkaitan
dengan penjadwalan dan pembayaran, yaitu `Schedule`, `Registration`,
`TherapyResult`, `Payment`, dan `Therapist`, beserta metode-metode operasional
yang diperlukan pada setiap kelas.

[Gambar 3.12 *Class Diagram* Iterasi 2]

**D. *User Interface* Iterasi 2**

[Gambar 3.13 *Wireframe* Halaman Kelola Jadwal Terapi]

[Gambar 3.14 *Wireframe* Halaman Lihat Jadwal (Orang Tua)]

[Gambar 3.15 *Wireframe* Halaman Lihat Jadwal (Terapis)]

[Gambar 3.16 *Wireframe* Halaman Catat Hasil Terapi]

[Gambar 3.17 *Wireframe* Halaman Pembayaran]

#### 3.4.2.2 *Unit Testing* 2

**Tabel 3.10 Hasil *Unit Testing* Iterasi 2**

| *Test Case*                         | *Test Scenario*                                                        | *Expected Result*                                            | *Result* |
|-------------------------------------|------------------------------------------------------------------------|--------------------------------------------------------------|:--------:|
| Kelola jadwal — tanpa konflik        | Admin membuat jadwal terapi pada waktu yang tersedia                   | Jadwal berhasil dibuat dan tampil di daftar jadwal           | Pass     |
| Kelola jadwal — jadwal bentrok       | Admin membuat jadwal terapis yang sudah memiliki jadwal di waktu sama  | Sistem menolak dengan pesan "Terdapat konflik jadwal"        | Pass     |
| Lihat jadwal — orang tua             | Orang tua membuka halaman jadwal terapi anak                           | Sistem menampilkan jadwal terapi anak yang bersangkutan saja | Pass     |
| Lihat jadwal — terapis               | Terapis membuka halaman jadwal pribadinya                              | Jadwal sesuai nama terapis yang *login* tampil dengan benar  | Pass     |
| Catat hasil terapi — data lengkap    | Terapis mengisi *form* hasil terapi dan menyimpan                      | Hasil terapi tersimpan dan terhubung dengan jadwal sesi      | Pass     |
| Pencatatan pembayaran — data valid   | Admin mencatat pembayaran dengan nominal dan metode pembayaran         | Pembayaran berhasil tercatat dan terhubung ke data pasien    | Pass     |
| Lihat bukti pembayaran — orang tua   | Orang tua mengakses halaman riwayat pembayaran                         | Riwayat pembayaran anak tampil dengan benar                  | Pass     |

#### 3.4.2.3 *Code* 2

[Screenshot Halaman Kelola Jadwal Terapi]

[Screenshot Halaman Lihat Jadwal (Orang Tua)]

[Screenshot Halaman Catat Hasil Terapi]

[Screenshot Halaman Pencatatan Pembayaran]

[Screenshot Halaman Riwayat Pembayaran]

#### 3.4.2.4 *Refactoring* 2

Proses *refactoring* pada iterasi 2 berfokus pada:

1. Optimasi *query* basis data untuk pengecekan konflik jadwal menggunakan
   *index* yang tepat pada kolom `schedule_date` dan `start_time`.
2. Ekstraksi logika penjadwalan ke dalam *dedicated Service Class*
   (`ScheduleService`) agar dapat diuji dan digunakan kembali secara mandiri.
3. Pembuatan komponen Vue.js yang dapat digunakan ulang untuk tampilan kalender
   jadwal dan tabel riwayat pembayaran.

#### 3.4.2.5 *System Testing* 2

**Tabel 3.11 Hasil *System Testing* Iterasi 2**

| *Test Case*            | *Test Scenario*                                           | *Expected Result*                                               | *Tester*  | *Result* |
|------------------------|-----------------------------------------------------------|-----------------------------------------------------------------|-----------|:--------:|
| Kelola Jadwal Terapi   | Admin membuat jadwal terapi pasien tanpa bentrok          | Jadwal tersimpan dan tampil di halaman jadwal                   | Admin     | Pass     |
| Lihat Jadwal Terapi    | Orang tua membuka jadwal anak                            | Hanya jadwal anak sendiri yang ditampilkan                      | Orang Tua | Pass     |
| Lihat Jadwal Terapis   | Terapis membuka jadwal pribadinya                        | Jadwal harian terapis tampil dengan benar                       | Terapis   | Pass     |
| Catat Hasil Terapi     | Terapis mengisi hasil terapi setelah sesi                | Hasil tersimpan dan dapat dilihat oleh admin dan dokter         | Terapis   | Pass     |
| Pencatatan Pembayaran  | Admin mencatat pembayaran layanan pasien                 | Pembayaran tercatat dan terhubung ke data pasien                | Admin     | Pass     |
| Riwayat Pembayaran     | Admin membuka halaman riwayat dan filter pembayaran      | Daftar riwayat pembayaran tampil dengan benar                   | Admin     | Pass     |
| Lihat Bukti Pembayaran | Orang tua melihat status dan bukti pembayaran            | Status pembayaran dan detail tampil dengan benar                | Orang Tua | Pass     |

#### 3.4.2.6 *Retrospective* 2

Evaluasi iterasi 2 mencakup tinjauan terhadap fitur penjadwalan dan pembayaran
yang telah diimplementasikan. Mekanisme validasi konflik jadwal berjalan sesuai
yang diharapkan dan berhasil mengatasi permasalahan utama pada sistem sebelumnya
yang berbasis Google Spreadsheet.

Pada tahap *system testing*, ditemukan dua permasalahan yang kemudian diselesaikan
sebelum iterasi ini ditutup. Pertama, halaman *Lihat Jadwal Terapi* untuk aktor
orang tua tidak menampilkan jadwal yang tersedia meskipun data jadwal telah
tersimpan di basis data. Permasalahan ini disebabkan oleh kesalahan *query* pada
sisi *backend* yang tidak menyertakan relasi ke data pasien milik orang tua yang
sedang *login*, sehingga hasil yang dikembalikan selalu kosong. Perbaikan dilakukan
dengan memperbaiki *query* agar menyertakan filter berdasarkan `guardian_id` milik
pengguna yang sedang *login*, dan pengujian ulang memastikan fungsionalitas berjalan
dengan benar.

Kedua, fitur *Riwayat Pembayaran* (US-08) sempat mengalami keterlambatan karena
tampilan antarmuka belum selesai diimplementasikan. Setelah dilakukan perbaikan
dengan melengkapi halaman riwayat pembayaran beserta fitur filter, pengujian ulang
menghasilkan status *Pass*. Seluruh *test case* pada *system testing* iterasi 2
akhirnya berhasil dipenuhi.

Masukan lain dari pengujian mencakup penambahan fitur pengingat jadwal dan
penyederhanaan tampilan kalender pada perangkat *mobile*. Catatan tersebut akan
menjadi pertimbangan pada pengembangan lanjutan.

---

### 3.4.3 Iterasi 3

Iterasi 3 berfokus pada implementasi fitur rekam medis elektronik oleh dokter,
evaluasi perkembangan anak oleh psikolog menggunakan instrumen KPSP,
pengelolaan riwayat layanan oleh seluruh tenaga medis, serta pembuatan laporan
layanan kesehatan secara terintegrasi. Penentuan prioritas *user story* pada iterasi
ketiga dilakukan menggunakan metode MoSCoW, dengan rincian yang dapat dilihat
pada Tabel 3.12 berikut.

**Tabel 3.12 *User Story* Iterasi 3**

| *User Story* | Judul                       | Aktor        | Prioritas MoSCoW | Task                                                              |
|:------------:|-----------------------------|--------------|:----------------:|-------------------------------------------------------------------|
| US-14        | Kelola Rekam Medis          | Dokter       | *Must Have*      | Pembuatan modul rekam medis elektronik sesuai Permenkes 24/2022  |
| US-15        | Evaluasi Perkembangan Anak  | Psikolog     | *Must Have*      | Pembuatan modul *screening* KPSP dengan perhitungan skor otomatis|
| US-16        | Riwayat Layanan Pasien      | Tenaga Medis | *Should Have*    | Pembuatan halaman *timeline* riwayat layanan pasien              |
| US-17        | Laporan Layanan             | Admin        | *Must Have*      | Pembuatan modul laporan terintegrasi dengan ekspor PDF           |
| US-18        | Pencarian Data Pasien       | Admin        | *Could Have*     | Pembuatan fitur pencarian lanjutan berdasarkan berbagai kriteria |
| US-19        | Kuesioner SDQ               | Orang Tua    | *Should Have*    | Pembuatan modul kuesioner SDQ dengan perhitungan skor otomatis dan akses hasil bagi dokter & psikolog |

#### 3.4.3.1 *Design* 3

Tahap *design* pada iterasi ketiga berfokus pada perancangan modul rekam medis
elektronik yang memenuhi standar Permenkes Nomor 24 Tahun 2022, modul evaluasi
perkembangan anak menggunakan instrumen KPSP, dan sistem pelaporan layanan
kesehatan yang terintegrasi.

**A. *Use Case Diagram* Iterasi 3**

*Use case diagram* pada iterasi ini menggambarkan interaksi dokter dalam
mengelola rekam medis, psikolog dalam melakukan evaluasi dan *screening* KPSP,
seluruh tenaga medis dalam mengakses riwayat layanan pasien, serta admin dalam
menghasilkan laporan layanan. Fitur pencarian data pasien memungkinkan admin
menemukan informasi pasien secara cepat berdasarkan berbagai kriteria seperti
nama, nomor rekam medis, dan tanggal lahir.

[Gambar 3.18 *Use Case Diagram* Iterasi 3]

**B. *Entity Relationship Diagram* Iterasi 3**

ERD pada iterasi ketiga diperluas dengan menambahkan entitas `medical_records`,
`kpsp_screenings`, `doctors`, dan `psychologists`. Entitas `medical_records`
memiliki relasi *many-to-one* dengan `patients` dan `doctors`, serta menyimpan
informasi anamnesis, diagnosis dengan kode ICD-10, dan rencana penanganan.
Entitas `kpsp_screenings` menyimpan hasil skrining perkembangan anak berdasarkan
usia dalam bulan, skor total, dan rekomendasi tindak lanjut.

[Gambar 3.19 ERD Iterasi 3]

**Tabel 3.13 Kamus Data dan Fungsi Iterasi 3**

| No | Entitas           | Atribut              | Tipe Data                                        |
|:--:|-------------------|----------------------|--------------------------------------------------|
| 1  | medical_records   | id                   | bigInt                                           |
|    |                   | patient_id           | bigInt                                           |
|    |                   | doctor_id            | bigInt                                           |
|    |                   | visit_date           | date                                             |
|    |                   | chief_complaint      | text                                             |
|    |                   | physical_examination | text                                             |
|    |                   | diagnosis            | text                                             |
|    |                   | icd_code             | varchar(20)                                      |
|    |                   | treatment_plan       | text                                             |
|    |                   | notes                | text                                             |
|    |                   | created_at           | timestamp                                        |
|    |                   | updated_at           | timestamp                                        |
| 2  | kpsp_screenings   | id                   | bigInt                                           |
|    |                   | patient_id           | bigInt                                           |
|    |                   | psychologist_id      | bigInt                                           |
|    |                   | screening_date       | date                                             |
|    |                   | age_months           | integer                                          |
|    |                   | total_score          | integer                                          |
|    |                   | result               | enum('sesuai','meragukan','penyimpangan')        |
|    |                   | notes                | text                                             |
|    |                   | recommendations      | text                                             |
|    |                   | created_at           | timestamp                                        |
|    |                   | updated_at           | timestamp                                        |
| 3  | doctors           | id                   | bigInt                                           |
|    |                   | user_id              | bigInt                                           |
|    |                   | name                 | varchar(100)                                     |
|    |                   | specialization       | varchar(100)                                     |
|    |                   | license_number       | varchar(50)                                      |
|    |                   | phone                | varchar(20)                                      |
|    |                   | is_active            | boolean                                          |
|    |                   | created_at           | timestamp                                        |
|    |                   | updated_at           | timestamp                                        |
| 4  | psychologists     | id                   | bigInt                                           |
|    |                   | user_id              | bigInt                                           |
|    |                   | name                 | varchar(100)                                     |
|    |                   | specialization       | varchar(100)                                     |
|    |                   | license_number       | varchar(50)                                      |
|    |                   | phone                | varchar(20)                                      |
|    |                   | is_active            | boolean                                          |
|    |                   | created_at           | timestamp                                        |
|    |                   | updated_at           | timestamp                                        |

**C. *Class Diagram* Iterasi 3**

*Class diagram* pada iterasi 3 menambahkan kelas-kelas `MedicalRecord`,
`KpspScreening`, `Doctor`, dan `Psychologist`, beserta metode-metode yang
diperlukan untuk pengelolaan rekam medis, pelaksanaan *screening*, dan pembuatan
laporan layanan kesehatan.

[Gambar 3.20 *Class Diagram* Iterasi 3]

**D. *User Interface* Iterasi 3**

[Gambar 3.21 *Wireframe* Halaman Rekam Medis Elektronik]

[Gambar 3.22 *Wireframe* Halaman *Screening* KPSP]

[Gambar 3.23 *Wireframe* Halaman Riwayat Layanan Pasien]

[Gambar 3.24 *Wireframe* Halaman Laporan Layanan Kesehatan]

#### 3.4.3.2 *Unit Testing* 3

**Tabel 3.14 Hasil *Unit Testing* Iterasi 3**

| *Test Case*                            | *Test Scenario*                                                          | *Expected Result*                                                     | *Result* |
|----------------------------------------|--------------------------------------------------------------------------|-----------------------------------------------------------------------|:--------:|
| Kelola rekam medis — tambah baru        | Dokter mengisi *form* rekam medis pasien lengkap termasuk kode ICD-10    | Rekam medis tersimpan dan dapat diakses kembali                       | Pass     |
| Kelola rekam medis — edit data          | Dokter memperbarui diagnosis pada rekam medis yang sudah ada             | Perubahan tersimpan dengan *timestamp* pembaruan                      | Pass     |
| Evaluasi KPSP — isi kuesioner           | Psikolog mengisi instrumen KPSP sesuai usia anak                         | Skor dihitung otomatis dan hasil kategori tampil                      | Pass     |
| Evaluasi KPSP — usia di luar rentang   | Psikolog memasukkan usia di luar rentang instrumen                       | Sistem menampilkan pesan bahwa instrumen tidak tersedia untuk usia tsb| Pass     |
| Riwayat layanan — tampilan *timeline*   | Dokter membuka halaman riwayat pasien tertentu                           | Seluruh riwayat layanan pasien tampil berurutan berdasarkan tanggal   | Pass     |
| Laporan layanan — filter periode        | Admin memilih rentang tanggal dan menghasilkan laporan                   | Laporan berisi data sesuai periode yang dipilih                       | Pass     |
| Laporan layanan — ekspor PDF            | Admin mengekspor laporan ke format PDF                                   | File PDF berhasil diunduh dengan data yang lengkap dan akurat         | Pass     |
| Pencarian pasien — berdasarkan nama     | Admin mencari pasien menggunakan nama lengkap                            | Sistem menampilkan hasil pencarian yang relevan                       | Pass     |
| Pencarian pasien — nomor rekam medis    | Admin mencari menggunakan nomor rekam medis                              | Sistem menampilkan data pasien yang tepat                             | Pass     |
| Kuesioner SDQ — isi lengkap             | Orang tua mengisi semua 25 pertanyaan SDQ dan menekan simpan            | Skor per subskala dan total dihitung otomatis, interpretasi tampil    | Pass     |
| Kuesioner SDQ — submit belum lengkap    | Orang tua mencoba menekan simpan dengan pertanyaan yang belum dijawab   | Tombol simpan nonaktif, muncul peringatan jumlah pertanyaan tersisa   | Pass     |
| Kuesioner SDQ — interpretasi normal     | Total skor ≤15 setelah mengisi kuesioner                                | Interpretasi "Normal" tampil dengan *badge* hijau                     | Pass     |
| Kuesioner SDQ — interpretasi abnormal   | Total skor ≥20 setelah mengisi kuesioner                                | Interpretasi "Abnormal" tampil dengan *badge* merah                   | Pass     |
| Lihat hasil SDQ — dokter                | Dokter membuka halaman hasil SDQ pasien melalui menu rekam medis        | Hasil SDQ tampil dalam mode *read-only* dengan skor dan grafik        | Pass     |

#### 3.4.3.3 *Code* 3

[Screenshot Halaman Rekam Medis Elektronik]

[Screenshot *Form* Rekam Medis — Tab Anamnesis dan Diagnosis]

[Screenshot Halaman *Screening* KPSP]

[Screenshot Hasil *Screening* dan Rekomendasi]

[Screenshot Halaman Riwayat Layanan Pasien (*Timeline*)]

[Screenshot Halaman Laporan Layanan Kesehatan]

[Screenshot Fitur Pencarian Data Pasien]

#### 3.4.3.4 *Refactoring* 3

Proses *refactoring* pada iterasi 3 berfokus pada:

1. Optimasi *query* pelaporan menggunakan *eager loading* dan agregasi data di
   sisi basis data untuk meningkatkan performa pengambilan data laporan dalam
   jumlah besar.
2. Ekstraksi logika perhitungan skor KPSP ke dalam *dedicated Service Class*
   (`KpspService`) agar dapat diuji secara independen.
3. Penerapan *caching* pada data laporan yang sering diakses untuk mengurangi
   beban *query* basis data.
4. Pembuatan komponen Vue.js yang dapat digunakan ulang untuk tampilan *timeline*
   riwayat dan formulir rekam medis.

#### 3.4.3.5 *System Testing* 3

**Tabel 3.15 Hasil *System Testing* Iterasi 3**

| *Test Case*           | *Test Scenario*                                              | *Expected Result*                                                  | *Tester* | *Result* |
|-----------------------|--------------------------------------------------------------|--------------------------------------------------------------------|----------|:--------:|
| Kelola Rekam Medis    | Dokter mengisi dan menyimpan rekam medis pasien              | Rekam medis tersimpan dan terhubung ke data pasien                 | Dokter   | Pass     |
| Evaluasi KPSP         | Psikolog melakukan *screening* KPSP sesuai usia anak         | Skor dihitung otomatis dan rekomendasi tampil                      | Psikolog | Pass     |
| Riwayat Layanan       | Tenaga medis membuka riwayat layanan pasien                  | Seluruh riwayat layanan tampil dalam satu halaman                  | Dokter   | Pass     |
| Laporan Layanan       | Admin menghasilkan laporan bulanan dan mengekspor ke PDF     | Laporan berhasil dibuat dan dapat diunduh dalam format PDF         | Admin    | Pass     |
| Pencarian Pasien      | Admin mencari pasien menggunakan nomor rekam medis           | Hasil pencarian akurat dan cepat ditampilkan                       | Admin    | Pass     |
| Kuesioner SDQ         | Orang tua mengisi dan menyimpan kuesioner SDQ untuk anaknya  | Skor dihitung otomatis dan hasil dapat dilihat di riwayat          | Orang Tua| Pass     |
| Lihat Hasil SDQ       | Dokter membuka hasil SDQ pasien melalui rekam medis          | Data SDQ tampil lengkap dalam mode *read-only*                     | Dokter   | Pass     |

#### 3.4.3.6 *Retrospective* 3

*Retrospective* pada iterasi 3 merupakan evaluasi akhir dari seluruh siklus
pengembangan prototipe SI-LKABK. Seluruh *user story* yang direncanakan berhasil
diimplementasikan dan lulus pengujian fungsional. Sistem secara keseluruhan mampu
mengintegrasikan seluruh modul layanan dalam satu platform yang terpusat,
mengatasi permasalahan utama UPTD BLKABK terkait pencatatan manual, konflik
penjadwalan, dan pelaporan yang memakan waktu. Rekam medis elektronik yang
dikembangkan dirancang sesuai dengan standar Permenkes Nomor 24 Tahun 2022 dan
siap untuk diterapkan pada lingkungan produksi setelah melalui tahap *deployment*.

---

### 3.4.4 *Deployment*

*Deployment* merupakan tahap penerapan sistem ke lingkungan operasional agar
dapat diakses secara langsung oleh seluruh pengguna di UPTD BLKABK Kota
Balikpapan. Proses *deployment* dilakukan setelah seluruh iterasi selesai dan
sistem dinyatakan layak berdasarkan hasil *system testing* iterasi 3.

Proses *deployment* yang dilakukan mencakup:

1. **Konfigurasi *server* produksi**: Konfigurasi *web server* Apache dan PHP 8.2
   pada *server* yang disediakan oleh UPTD BLKABK.
2. **Migrasi basis data**: Eksekusi migrasi *database* PostgreSQL dan pengisian
   data awal (*seeding*) untuk data master seperti jenis terapi dan akun pengguna
   awal.
3. **Unggah dan konfigurasi aplikasi**: Unggah kode aplikasi, konfigurasi
   *environment variables* (`.env`), dan kompilasi aset *frontend* untuk mode
   produksi menggunakan `npm run build`.
4. **Pengujian pasca-*deployment***: Verifikasi bahwa seluruh fitur berjalan
   dengan baik pada lingkungan produksi, termasuk koneksi basis data dan performa
   sistem.
5. **Penyusunan panduan pengguna (*guidebook*)**: Pembuatan dokumentasi
   penggunaan sistem untuk masing-masing peran pengguna yang berisi petunjuk
   operasional bertahap, mulai dari proses *login*, penggunaan fitur utama, hingga
   alur kerja sistem sesuai peran.
