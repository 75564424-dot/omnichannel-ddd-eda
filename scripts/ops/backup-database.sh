#!/usr/bin/env bash
set -euo pipefail

# MySQL backup script — Plan_Cloud Fase 2
# Usage: DB_HOST=127.0.0.1 DB_DATABASE=platform DB_USERNAME=platform DB_PASSWORD=secret ./scripts/ops/backup-database.sh

BACKUP_DIR="${BACKUP_DIR:-./storage/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

: "${DB_HOST:?DB_HOST required}"
: "${DB_DATABASE:?DB_DATABASE required}"
: "${DB_USERNAME:?DB_USERNAME required}"
: "${DB_PASSWORD:?DB_PASSWORD required}"

mkdir -p "${BACKUP_DIR}"
TS="$(date -u +%Y%m%d_%H%M%S)"
OUT="${BACKUP_DIR}/${DB_DATABASE}_${TS}.sql.gz"

echo "Backing up ${DB_DATABASE}@${DB_HOST} -> ${OUT}"
mysqldump -h "${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" \
  --single-transaction \
  --routines \
  --triggers \
  "${DB_DATABASE}" | gzip > "${OUT}"

find "${BACKUP_DIR}" -name '*.sql.gz' -mtime +"${RETENTION_DAYS}" -delete 2>/dev/null || true

echo "Backup complete: ${OUT}"
