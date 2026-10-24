# Activity Diagram — SI-LKABK

---

## US-01 — Login Sistem

```plantuml
@startuml
title US-01: Login Sistem
start
:Buka halaman login;
:Masukkan email dan password;
:Klik tombol Login;
:Sistem memvalidasi kredensial;
if (Kredensial valid?) then (tidak)
  :Tampilkan pesan "Email atau password salah";
  stop
else (ya)
  :Identifikasi peran pengguna;
  :Redirect ke dashboard sesuai peran;
endif
stop
@enduml
```

---

## US-02 — Logout Sistem

```plantuml
@startuml
title US-02: Logout Sistem
start
:Klik tombol Logout;
:Sistem menginvalidasi sesi pengguna;
:Menghapus token sesi;
:Redirect ke halaman login;
stop
@enduml
```

---

## US-03 — Kelola Akun Pengguna

```plantuml
@startuml
title US-03: Kelola Akun Pengguna
start
:Buka halaman manajemen pengguna;
:Tampilkan daftar akun pengguna;
if (Pilih aksi?) then (Tambah)
  :Isi formulir nama, email, peran, password;
  :Sistem memvalidasi data;
  if (Data valid?) then (tidak)
    :Tampilkan pesan kesalahan validasi;
    stop
  else (ya)
    :Simpan akun pengguna baru;
  endif
elseif (Edit) then (Edit)
  :Pilih akun yang ingin diubah;
  :Ubah data pada formulir;
  :Simpan perubahan;
elseif (Nonaktifkan) then (Nonaktifkan)
  :Pilih akun yang ingin dinonaktifkan;
  :Konfirmasi tindakan;
  :Ubah status akun menjadi nonaktif;
endif
:Tampilkan notifikasi berhasil;
stop
@enduml
```

---

## US-04 — Pendaftaran Pasien

```plantuml
@startuml
title US-04: Pendaftaran Pasien
start
:Buka formulir pendaftaran pasien baru;
:Isi data pasien (nama, NIK, tanggal lahir, alamat, diagnosis);
:Isi data wali/orang tua (nama, hubungan, telepon);
:Klik tombol Simpan;
:Sistem memvalidasi data;
if (Data valid?) then (tidak)
  :Tampilkan pesan kesalahan;
  stop
else (ya)
  :Buat nomor rekam medis otomatis (format RM-YYYYMMDD-XXXX);
  :Simpan data pasien dan data wali ke basis data;
  :Tampilkan notifikasi pendaftaran berhasil;
endif
stop
@enduml
```

---

## US-05 — Pendaftaran Layanan

```plantuml
@startuml
title US-05: Pendaftaran Layanan
start
:Orang tua membuka halaman pendaftaran layanan;
:Pilih anak yang akan didaftarkan;
:Pilih jenis layanan/terapi;
:Isi keterangan tambahan (opsional);
:Klik tombol Daftar;
:Sistem memvalidasi data;
if (Data valid?) then (tidak)
  :Tampilkan pesan kesalahan;
  stop
else (ya)
  :Simpan data pendaftaran layanan;
  :Tampilkan konfirmasi pendaftaran berhasil;
endif
stop
@enduml
```

---

## US-06 — Kelola Data Pasien

```plantuml
@startuml
title US-06: Kelola Data Pasien
start
:Buka halaman daftar pasien;
:Gunakan fitur pencarian atau filter (nama/RM/status);
:Tampilkan daftar pasien sesuai filter;
if (Pilih aksi?) then (Lihat Detail)
  :Tampilkan profil lengkap pasien;
elseif (Edit) then (Edit)
  :Buka formulir edit data pasien;
  :Perbarui data yang diperlukan;
  :Simpan perubahan;
elseif (Nonaktifkan) then (Nonaktifkan)
  :Konfirmasi tindakan;
  :Ubah status pasien menjadi nonaktif;
endif
:Tampilkan notifikasi berhasil;
stop
@enduml
```

---

## US-07 — Pencatatan Pembayaran

```plantuml
@startuml
title US-07: Pencatatan Pembayaran
start
:Buka halaman pencatatan pembayaran;
:Cari dan pilih pasien;
:Pilih layanan yang dibayarkan;
:Masukkan nominal pembayaran;
:Pilih metode pembayaran (tunai/transfer);
:Klik tombol Simpan;
:Sistem memvalidasi data pembayaran;
if (Data valid?) then (tidak)
  :Tampilkan pesan kesalahan;
  stop
else (ya)
  :Simpan data transaksi pembayaran;
  :Tampilkan konfirmasi dan ringkasan pembayaran;
endif
stop
@enduml
```

---

## US-08 — Riwayat Pembayaran

```plantuml
@startuml
title US-08: Riwayat Pembayaran
start
:Buka halaman riwayat pembayaran;
:Terapkan filter (periode, nama pasien, metode);
:Sistem mengambil data sesuai filter;
:Tampilkan daftar transaksi pembayaran;
if (Lihat detail transaksi?) then (ya)
  :Tampilkan detail pembayaran;
else (tidak)
endif
stop
@enduml
```

---

## US-09 — Lihat Bukti Pembayaran

```plantuml
@startuml
title US-09: Lihat Bukti Pembayaran
start
:Orang tua membuka halaman riwayat pembayaran;
:Sistem menampilkan daftar transaksi anak;
:Pilih salah satu transaksi;
:Tampilkan detail dan bukti pembayaran;
stop
@enduml
```

---

## US-10 — Kelola Jadwal Terapi

```plantuml
@startuml
title US-10: Kelola Jadwal Terapi
start
:Buka halaman penjadwalan terapi;
:Pilih pasien;
:Pilih terapis dan jenis terapi;
:Tentukan hari dan jam sesi;
:Sistem memeriksa konflik jadwal terapis;
if (Ada konflik jadwal?) then (ya)
  :Tampilkan notifikasi konflik;
  :Minta admin memilih waktu lain;
  stop
else (tidak)
  :Simpan jadwal terapi;
  :Tampilkan konfirmasi jadwal berhasil dibuat;
endif
stop
@enduml
```

---

## US-11 — Lihat Jadwal Terapi

```plantuml
@startuml
title US-11: Lihat Jadwal Terapi
start
:Orang tua membuka halaman jadwal terapi;
:Sistem menampilkan jadwal terapi anak;
if (Pilih tampilan?) then (Kalender)
  :Tampilkan jadwal dalam tampilan kalender;
else (Daftar)
  :Tampilkan jadwal dalam tampilan daftar;
endif
:Lihat detail sesi terapi;
stop
@enduml
```

---

## US-12 — Lihat Jadwal Terapis

```plantuml
@startuml
title US-12: Lihat Jadwal Terapis
start
:Terapis membuka halaman jadwal personal;
:Sistem mengambil jadwal milik terapis yang login;
:Tampilkan jadwal sesi dalam tampilan kalender/daftar;
:Lihat detail pasien dan jenis terapi per sesi;
stop
@enduml
```

---

## US-13 — Catat Hasil Terapi

```plantuml
@startuml
title US-13: Catat Hasil Terapi
start
:Terapis membuka daftar jadwal hari ini;
:Pilih sesi terapi yang akan dicatat;
:Isi formulir hasil terapi (perkembangan, catatan, rekomendasi);
:Klik tombol Simpan;
:Sistem memvalidasi data;
if (Data valid?) then (tidak)
  :Tampilkan pesan kesalahan;
  stop
else (ya)
  :Simpan hasil terapi ke basis data;
  :Tampilkan notifikasi berhasil;
endif
stop
@enduml
```

---

## US-14 — Kelola Rekam Medis

```plantuml
@startuml
title US-14: Kelola Rekam Medis
start
:Dokter membuka daftar pasien;
:Pilih pasien yang akan diperiksa;
:Buka halaman rekam medis pasien;
if (Pilih aksi?) then (Tambah baru)
  :Isi formulir rekam medis;
  :Masukkan anamnesis, diagnosis, kode ICD-10, rencana penanganan;
  :Sistem memvalidasi data;
  if (Data valid?) then (tidak)
    :Tampilkan pesan kesalahan;
    stop
  else (ya)
    :Simpan rekam medis baru;
  endif
elseif (Edit) then (Edit)
  :Pilih entri rekam medis yang ada;
  :Perbarui data diagnosis atau catatan;
  :Simpan perubahan;
endif
:Tampilkan konfirmasi berhasil;
stop
@enduml
```

---

## US-15 — Evaluasi Perkembangan Anak (KPSP)

```plantuml
@startuml
title US-15: Evaluasi Perkembangan Anak (KPSP)
start
:Psikolog membuka halaman skrining KPSP;
:Pilih pasien yang akan dievaluasi;
:Sistem menghitung usia anak dalam bulan;
if (Usia dalam rentang instrumen KPSP?) then (tidak)
  :Tampilkan pesan instrumen tidak tersedia untuk usia ini;
  stop
else (ya)
  :Tampilkan instrumen KPSP sesuai usia;
  :Psikolog mengisi jawaban untuk setiap pertanyaan;
  :Sistem menghitung skor total secara otomatis;
  :Tentukan kategori hasil (Sesuai/Meragukan/Penyimpangan);
  :Tampilkan hasil dan rekomendasi tindak lanjut;
  :Simpan hasil skrining ke rekam medis;
endif
stop
@enduml
```

---

## US-16 — Riwayat Layanan Pasien

```plantuml
@startuml
title US-16: Riwayat Layanan Pasien
start
:Tenaga medis membuka halaman riwayat layanan;
:Cari pasien berdasarkan nama atau nomor rekam medis;
:Pilih pasien dari hasil pencarian;
:Sistem mengambil seluruh riwayat layanan pasien;
:Tampilkan timeline riwayat (terapi, rekam medis, skrining, pembayaran);
if (Pilih entri tertentu?) then (ya)
  :Tampilkan detail entri layanan;
else (tidak)
endif
stop
@enduml
```

---

## US-17 — Laporan Layanan

```plantuml
@startuml
title US-17: Laporan Layanan
start
:Admin membuka modul laporan layanan;
:Pilih jenis laporan (kunjungan/pembayaran/terapi);
:Tentukan rentang periode laporan;
:Klik tombol Generate Laporan;
:Sistem mengagregasi data sesuai filter;
:Tampilkan ringkasan dan tabel data laporan;
if (Ekspor laporan?) then (ya)
  :Sistem menghasilkan file PDF;
  :File PDF diunduh oleh admin;
else (tidak)
endif
stop
@enduml
```

---

## US-18 — Pencarian Data Pasien

```plantuml
@startuml
title US-18: Pencarian Data Pasien
start
:Admin membuka fitur pencarian pasien;
:Masukkan kata kunci pencarian (nama / nomor rekam medis);
:Sistem melakukan pencarian ke basis data;
if (Data ditemukan?) then (tidak)
  :Tampilkan pesan "Data tidak ditemukan";
  stop
else (ya)
  :Tampilkan daftar hasil pencarian yang relevan;
  if (Pilih pasien?) then (ya)
    :Tampilkan profil lengkap pasien;
  else (tidak)
  endif
endif
stop
@enduml
```
