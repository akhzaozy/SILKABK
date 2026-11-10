# Deploy SILKABK + SPK Skrining Anak

Panduan menjalankan dua sistem yang saling tersambung dalam satu stack Docker:

- **SILKABK**: sistem utama (repo `bucks23/SILKABK`, branch `testing-deploy`)
- **SPK Skrining Anak**: sistem skrining (repo `UstadzApuy/spk-skrining-anak-v2`, branch `testing-deploy`)

SPK membaca database SILKABK (user & pasien) dan menerima login otomatis (SSO) dari SILKABK.

## Susunan

```
                         ┌─ silkabk.domain.id ─► silkabk-frontend (Nginx) ─► silkabk-backend (PHP-FPM) ─┐
Reverse proxy (HTTPS) ───┤                                                                               ├─► postgres
milik server             └─ spk.domain.id ─────► spk-frontend (Nginx) ─────► spk-backend (PHP-FPM) ─────┘   ├ ta_bismillah (SILKABK)
                                                                                                             └ spk_uptd     (SPK)
```

| Service | Isi | Port |
|---|---|---|
| `postgres` | PostgreSQL 18, dua database | tidak dibuka ke host |
| `silkabk-backend` | Laravel SILKABK (PHP-FPM) | internal 9000 |
| `silkabk-frontend` | Nginx: hasil build Vue + teruskan ke backend | `127.0.0.1:8080` |
| `spk-backend` | Laravel SPK (PHP-FPM) | internal 9000 |
| `spk-frontend` | Nginx: hasil build Vue + teruskan ke backend | `127.0.0.1:8081` |

Frontend (Vue) di kedua sistem **tidak** berjalan sebagai aplikasi Node terpisah: Vue di-build oleh Vite
saat `docker build`, lalu file statisnya disajikan Nginx. Dockerfile ada di masing-masing repo:
`docker/production/Dockerfile` dengan dua target, `backend` dan `frontend`.

## Kebutuhan

- Docker Engine + Docker Compose v2
- Reverse proxy dengan HTTPS (Nginx, Traefik, Nginx Proxy Manager, Caddy, dll.)
- Dua subdomain, mis. `silkabk.domain.id` dan `spk.domain.id`
  (**wajib beda host**, agar cookie login kedua sistem tidak saling menimpa)

## Langkah deploy pertama kali

### 1. Clone kedua repo bersebelahan

```bash
mkdir -p /opt/uptd && cd /opt/uptd
git clone -b testing-deploy https://github.com/bucks23/SILKABK.git
git clone -b testing-deploy https://github.com/UstadzApuy/spk-skrining-anak-v2.git
```

Hasilnya:

```
/opt/uptd/SILKABK/               <- berisi folder deploy/ ini dan ta_bismillah.sql
/opt/uptd/spk-skrining-anak-v2/  <- berisi Database_UPTD_BLK-ABK.sql
```

### 2. Isi setelan

```bash
cd /opt/uptd/SILKABK/deploy
cp .env.example .env
nano .env
```

Yang wajib diisi:

| Variabel | Isi |
|---|---|
| `SILKABK_URL`, `SPK_URL` | Alamat publik, mis. `https://silkabk.domain.id` dan `https://spk.domain.id` |
| `SILKABK_APP_KEY`, `SPK_APP_KEY` | Kunci aplikasi, **beda** untuk tiap sistem |
| `SSO_SECRET` | Secret login otomatis (satu nilai, dipakai kedua sistem) |
| `POSTGRES_PASSWORD`, `SILKABK_DB_PASSWORD`, `SPK_DB_PASSWORD`, `SILKABK_READER_PASSWORD` | Password database, huruf & angka saja |

Membuat kunci & secret:

```bash
docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"   # APP_KEY (jalankan 2x)
docker run --rm php:8.2-cli php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"                   # SSO_SECRET / password
```

### 3. Build & jalankan

```bash
docker compose up -d --build
docker compose logs -f postgres silkabk-backend spk-backend
```

Saat pertama kali (volume database masih kosong), container `postgres` otomatis:

1. Membuat user `silkabk_user`, `spk_user`, `silkabk_reader` (baca-saja) dan database `ta_bismillah`, `spk_uptd`
2. Me-restore `SILKABK/ta_bismillah.sql` ke `ta_bismillah`
3. Me-restore `spk-skrining-anak-v2/Database_UPTD_BLK-ABK.sql` ke `spk_uptd`
4. Memberi `silkabk_reader` hak SELECT ke database SILKABK

Setelah database siap, setiap container backend start:

- menjalankan `php artisan migrate --force` (SPK: menambah kolom penghubung ke SILKABK)
- membuat cache config/route/view
- **SPK saja:** menjalankan `php artisan silkabk:sync` (pasien & akun wali SILKABK masuk ke SPK)

Tunggu sampai log berisi `NOTICE: ready to handle connections` untuk kedua backend.

### 4. Arahkan reverse proxy

| Subdomain | Diteruskan ke |
|---|---|
| `silkabk.domain.id` | `http://127.0.0.1:8080` |
| `spk.domain.id` | `http://127.0.0.1:8081` |

Proxy wajib meneruskan header `Host`, `X-Forwarded-For`, dan `X-Forwarded-Proto`.
Contoh Nginx:

```nginx
server {
    server_name silkabk.domain.id;
    # ... setelan SSL ...
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
# Buat blok yang sama untuk spk.domain.id -> 127.0.0.1:8081
```

Kalau proxy berjalan sebagai container di jaringan Docker lain, ubah `SILKABK_BIND`/`SPK_BIND`
atau sambungkan service `*-frontend` ke jaringan proxy tersebut.

### 5. Uji

1. Buka `https://silkabk.domain.id`, login (psikolog / dokter / super_admin / admin / orang_tua)
2. Klik menu **SPK Skrining Anak** di sidebar (bagian *Sistem Lain*)
3. Harus langsung masuk ke dashboard SPK tanpa login ulang

Pemetaan role SILKABK → SPK:

| SILKABK | SPK |
|---|---|
| super_admin, psikolog | perawat |
| dokter | dokter |
| admin | administrator |
| orang_tua | orang_tua |
| terapis | tidak punya akses |

### 6. Setelah online

- **Ganti password akun bawaan.** Data awal berisi akun uji dengan password `password`.
- Atur backup rutin database:

```bash
docker compose exec -T postgres pg_dump -U postgres -Fc ta_bismillah > backup_silkabk_$(date +%F).backup
docker compose exec -T postgres pg_dump -U postgres -Fc spk_uptd     > backup_spk_$(date +%F).backup
```

## Update aplikasi

```bash
cd /opt/uptd/SILKABK && git pull
cd /opt/uptd/spk-skrining-anak-v2 && git pull
cd /opt/uptd/SILKABK/deploy && docker compose up -d --build
```

Migration baru dijalankan otomatis. **Database tidak di-restore ulang**: file dump hanya dipakai
saat volume `pgdata` masih kosong.

## Perintah berguna

```bash
docker compose ps                                              # status
docker compose logs -f spk-backend                             # log
docker compose exec spk-backend php artisan silkabk:sync       # sinkron manual pasien SILKABK -> SPK
docker compose exec silkabk-backend php artisan tinker         # konsol Laravel
docker compose restart silkabk-backend spk-backend             # setelah ubah .env
```

> Setelah mengubah `.env`, jalankan `docker compose up -d` (bukan hanya `restart`) agar nilai baru terbaca.

**Reset total** (menghapus semua data lalu restore ulang dari file dump):

```bash
docker compose down -v      # -v MENGHAPUS volume database & storage!
docker compose up -d --build
```

## Masalah umum

| Gejala | Penyebab / solusi |
|---|---|
| Backend berhenti dengan `APP_KEY belum diisi` | Isi `SILKABK_APP_KEY` / `SPK_APP_KEY` di `.env` |
| Klik menu SPK kembali ke login SPK "Tautan login tidak valid" | `SSO_SECRET` berubah tanpa restart, atau jam server tidak sinkron (token berlaku 60 detik) |
| Login di satu sistem membuat logout di sistem lain | Kedua sistem memakai host yang sama. Pakai dua subdomain berbeda |
| Link/redirect jadi `http://` | Proxy tidak mengirim `X-Forwarded-Proto` |
| Log postgres: error saat restore | Hapus volume (`docker compose down -v`), perbaiki file dump, lalu `up` lagi |
