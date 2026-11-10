#!/bin/bash
# Dijalankan otomatis oleh image postgres HANYA saat volume data masih kosong.
# 1. Me-restore dump database SILKABK (format custom pgAdmin atau SQL biasa)
# 2. Membuat user baca-saja "spk_reader" untuk sistem SPK Skrining Anak
set -euo pipefail

DUMP=/dumps/dump

if [ -s "$DUMP" ]; then
    if [ "$(head -c 5 "$DUMP")" = "PGDMP" ]; then
        echo "[init] Restore dump (format custom) ke $POSTGRES_DB..."
        pg_restore --no-owner --no-privileges --exit-on-error \
            --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" "$DUMP"
    else
        echo "[init] Restore dump (format SQL) ke $POSTGRES_DB..."
        sed -e '/OWNER TO /d' "$DUMP" \
            | psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB"
    fi
else
    echo "[init] File dump tidak ada; tabel akan dibuat oleh migration."
fi

if [ -n "${SPK_READER_PASSWORD:-}" ]; then
    echo "[init] Membuat user baca-saja spk_reader..."
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<SQL
CREATE ROLE spk_reader LOGIN PASSWORD '${SPK_READER_PASSWORD}';
GRANT CONNECT ON DATABASE "${POSTGRES_DB}" TO spk_reader;
GRANT USAGE ON SCHEMA public TO spk_reader;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO spk_reader;
ALTER DEFAULT PRIVILEGES FOR ROLE "${POSTGRES_USER}" IN SCHEMA public GRANT SELECT ON TABLES TO spk_reader;
SQL
fi

echo "[init] Selesai."
