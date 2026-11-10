# Deploy SILKABK (+ SPK Skrining Anak)

Dua repo, masing-masing punya `docker-compose.yml` sendiri di root dan di-deploy terpisah:

| Repo | Branch | Port host |
|---|---|---|
| `bucks23/SILKABK` (repo ini) | `testing-deploy` | frontend **8818**, backend **8817** |
| `UstadzApuy/spk-skrining-anak-v2` | `testing-deploy` | frontend **8810**, backend **8809** |

Keduanya tersambung lewat jaringan Docker **`uptd-network`** (dibuat oleh compose SILKABK):
SPK membaca database SILKABK (host `silkabk-db`, user baca-saja `spk_reader`) dan menerima
login otomatis (SSO) dari SILKABK. **Deploy SILKABK lebih dulu, baru SPK.**

```
                     ┌─ silkabk.domain.id ─► :8818 frontend ─► :8817 backend ─► db (ta_bismillah)
Reverse proxy HTTPS ─┤                                                            ▲ uptd-network
                     └─ spk.domain.id ─────► :8810 frontend ─► :8809 backend ─────┘ (baca-saja)
                                                                    └──────────► db (spk_uptd)
```

- **frontend** = Nginx: menyajikan hasil build Vue dan meneruskan request PHP. Ini yang dituju proxy.
- **backend** = PHP-FPM (Laravel). Protokol FastCGI, **bukan HTTP** (tidak bisa dibuka di browser).
- **db** = PostgreSQL 18. Saat pertama kali, otomatis me-restore file dump dari repo.

## 1. SILKABK

```bash
git clone -b testing-deploy https://github.com/bucks23/SILKABK.git
cd SILKABK
cp .env.example .env
nano .env
```

Isi/ubah di `.env`:

| Variabel | Isi |
|---|---|
| `APP_ENV` / `APP_DEBUG` / `LOG_LEVEL` | `production` / `false` / `warning` |
| `APP_URL` | `https://silkabk.domain.id` |
| `APP_KEY` | kunci baru (perintah di bawah) |
| `DB_USERNAME` / `DB_PASSWORD` | mis. `silkabk_user` / password kuat |
| `SPK_SKRINING_URL` | `https://spk.domain.id` |
| `SPK_SKRINING_SSO_SECRET` | secret SSO (**sama** dengan `SILKABK_SSO_SECRET` di SPK) |
| `SPK_READER_PASSWORD` | password user baca-saja untuk SPK |
| `SESSION_SECURE_COOKIE` | `true` |
| `FRONTEND_PORT` / `BACKEND_PORT` | `8818` / `8817` (sudah default) |

```bash
# kunci & secret
docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"   # APP_KEY
docker run --rm php:8.2-cli php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"                   # SSO secret / password

docker compose up -d --build
docker compose logs -f db backend     # tunggu "[init] Selesai." dan "ready to handle connections"
```

## 2. SPK Skrining Anak

```bash
git clone -b testing-deploy https://github.com/UstadzApuy/spk-skrining-anak-v2.git
cd spk-skrining-anak-v2
cp .env.example .env
nano .env
```

| Variabel | Isi |
|---|---|
| `APP_ENV` / `APP_DEBUG` / `LOG_LEVEL` | `production` / `false` / `warning` |
| `APP_URL` | `https://spk.domain.id` |
| `APP_KEY` | kunci baru (**beda** dari SILKABK) |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `spk_uptd` / `spk_user` / password kuat |
| `SILKABK_URL` | `https://silkabk.domain.id` |
| `SILKABK_SSO_SECRET` | **sama** dengan `SPK_SKRINING_SSO_SECRET` di SILKABK |
| `SILKABK_DB_USERNAME` | `spk_reader` |
| `SILKABK_DB_PASSWORD` | **sama** dengan `SPK_READER_PASSWORD` di SILKABK |
| `SESSION_SECURE_COOKIE` | `true` |
| `FRONTEND_PORT` / `BACKEND_PORT` | `8810` / `8809` (sudah default) |

```bash
docker compose up -d --build
docker compose logs -f db backend     # tunggu "N pasien SILKABK tersinkron ke SPK."
```

Saat start, backend SPK otomatis menjalankan migration (menambah kolom penghubung ke SILKABK)
lalu `php artisan silkabk:sync` (pasien & akun wali SILKABK masuk ke SPK).

## 3. Reverse proxy

| Subdomain | Diteruskan ke |
|---|---|
| `silkabk.domain.id` | `http://127.0.0.1:8818` |
| `spk.domain.id` | `http://127.0.0.1:8810` |

**Wajib dua host berbeda** — kalau keduanya di host yang sama (beda port saja), cookie login
saling menimpa. Proxy harus meneruskan `Host`, `X-Forwarded-For`, `X-Forwarded-Proto`:

```nginx
location / {
    proxy_pass http://127.0.0.1:8818;      # 8810 untuk SPK
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

## 4. Uji

Buka `https://silkabk.domain.id` → login → klik **SPK Skrining Anak** di sidebar
→ langsung masuk dashboard SPK tanpa login ulang.

| Role SILKABK | Di SPK |
|---|---|
| super_admin, psikolog | perawat |
| dokter | dokter |
| admin | administrator |
| orang_tua | orang_tua |
| terapis | tidak punya akses |

Setelah online: **ganti password akun bawaan** (data awal berisi akun uji).

## Perintah berguna (jalankan di folder repo masing-masing)

```bash
docker compose ps
docker compose logs -f backend
docker compose exec backend php artisan tinker
docker compose up -d                       # setelah mengubah .env
git pull && docker compose up -d --build   # update aplikasi
docker compose exec -T db pg_dump -U <DB_USERNAME> -Fc <DB_DATABASE> > backup_$(date +%F).backup
```

`docker compose down -v` **menghapus database** (lalu restore ulang dari file dump saat `up`).

## Masalah umum

| Gejala | Solusi |
|---|---|
| SPK: `network uptd-network ... not found` | Jalankan compose SILKABK dulu (atau `docker network create uptd-network`) |
| `required variable ... is missing a value` | Isi variabel itu di `.env` |
| Backend berhenti: `APP_KEY belum diisi` | Isi `APP_KEY` di `.env` |
| SPK: `sinkron SILKABK gagal` | Cek `SILKABK_DB_USERNAME=spk_reader` & `SILKABK_DB_PASSWORD` = `SPK_READER_PASSWORD` SILKABK; SILKABK harus sudah jalan |
| Klik menu SPK → "Tautan login tidak valid" | `SPK_SKRINING_SSO_SECRET` ≠ `SILKABK_SSO_SECRET`, atau jam server tidak sinkron |
| Login satu sistem membuat logout sistem lain | Pakai dua subdomain berbeda |
| Ubah password DB di `.env` tapi gagal konek | Password DB hanya dipakai saat volume pertama dibuat. Ubah lewat `ALTER ROLE`, atau `down -v` lalu `up` |
