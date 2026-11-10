#!/bin/bash
# Dijalankan otomatis oleh image postgres HANYA saat volume data masih kosong
# (pertama kali). Membuat user & database, lalu me-restore dump kedua sistem.
set -euo pipefail

echo "[init] Membuat user dan database..."
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname postgres <<SQL
CREATE ROLE silkabk_user   LOGIN PASSWORD '${SILKABK_DB_PASSWORD}';
CREATE ROLE spk_user       LOGIN PASSWORD '${SPK_DB_PASSWORD}';
CREATE ROLE silkabk_reader LOGIN PASSWORD '${SILKABK_READER_PASSWORD}';

CREATE DATABASE ta_bismillah OWNER silkabk_user;
CREATE DATABASE spk_uptd     OWNER spk_user;

REVOKE CONNECT ON DATABASE ta_bismillah FROM PUBLIC;
REVOKE CONNECT ON DATABASE spk_uptd     FROM PUBLIC;
GRANT  CONNECT ON DATABASE ta_bismillah TO silkabk_user, silkabk_reader;
GRANT  CONNECT ON DATABASE spk_uptd     TO spk_user;
SQL

# ─── Restore database SILKABK ───────────────────────────────
if [ -s /dumps/silkabk.dump ]; then
    if [ "$(head -c 5 /dumps/silkabk.dump)" = "PGDMP" ]; then
        echo "[init] Restore SILKABK (format custom) ke ta_bismillah..."
        pg_restore --no-owner --no-privileges --exit-on-error \
            --username silkabk_user --dbname ta_bismillah /dumps/silkabk.dump
    else
        echo "[init] Restore SILKABK (format SQL) ke ta_bismillah..."
        psql -v ON_ERROR_STOP=1 --username silkabk_user --dbname ta_bismillah -f /dumps/silkabk.dump
    fi
else
    echo "[init] Dump SILKABK tidak ada; tabel dibuat oleh migration."
fi

# ─── Restore database SPK ───────────────────────────────────
if [ -s /dumps/spk.sql ]; then
    if [ "$(head -c 5 /dumps/spk.sql)" = "PGDMP" ]; then
        echo "[init] Restore SPK (format custom) ke spk_uptd..."
        pg_restore --no-owner --no-privileges --exit-on-error \
            --username spk_user --dbname spk_uptd /dumps/spk.sql
    else
        echo "[init] Restore SPK (format SQL) ke spk_uptd..."
        psql -v ON_ERROR_STOP=1 --username spk_user --dbname spk_uptd -f /dumps/spk.sql
    fi
else
    echo "[init] Dump SPK tidak ada; tabel dibuat oleh migration."
fi

# ─── Hak baca-saja untuk SPK ke database SILKABK ────────────
echo "[init] Memberi hak SELECT ke silkabk_reader..."
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname ta_bismillah <<SQL
GRANT USAGE ON SCHEMA public TO silkabk_reader;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO silkabk_reader;
ALTER DEFAULT PRIVILEGES FOR ROLE silkabk_user IN SCHEMA public GRANT SELECT ON TABLES TO silkabk_reader;
SQL

echo "[init] Selesai."
