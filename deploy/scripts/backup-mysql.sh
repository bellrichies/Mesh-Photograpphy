#!/usr/bin/env bash
# Mesh Photography — Daily MySQL backup (PL-11)
#
# Schedule with cron (daily at 02:00):
#   0 2 * * * /var/www/meshphoto/deploy/scripts/backup-mysql.sh >> /var/log/meshphoto-backup.log 2>&1
#
# Prerequisites: mysqldump, gzip, aws CLI (if S3_BUCKET is set)

set -euo pipefail

# ── Configuration ─────────────────────────────────────────────────────────────
DB_NAME="${DB_DATABASE:-mesh_photography}"
DB_USER="${DB_USERNAME:-meshphoto_user}"
DB_PASS="${DB_PASSWORD:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

BACKUP_DIR="${BACKUP_DIR:-/var/backups/meshphoto}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"

# Optional: set to an S3 bucket name to also upload backups off-site
S3_BUCKET="${S3_BUCKET:-}"

# ── Setup ─────────────────────────────────────────────────────────────────────
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
FILENAME="meshphoto_${TIMESTAMP}.sql.gz"
FILEPATH="${BACKUP_DIR}/${FILENAME}"

mkdir -p "${BACKUP_DIR}"
chmod 700 "${BACKUP_DIR}"

# ── Dump ──────────────────────────────────────────────────────────────────────
echo "[$(date -u +%FT%TZ)] Starting backup → ${FILEPATH}"

MYSQL_PWD="${DB_PASS}" mysqldump \
    --host="${DB_HOST}" \
    --port="${DB_PORT}" \
    --user="${DB_USER}" \
    --single-transaction \
    --routines \
    --triggers \
    --set-gtid-purged=OFF \
    "${DB_NAME}" | gzip -9 > "${FILEPATH}"

echo "[$(date -u +%FT%TZ)] Dump complete ($(du -sh "${FILEPATH}" | cut -f1))"

# ── Verify ────────────────────────────────────────────────────────────────────
if ! gzip -t "${FILEPATH}"; then
    echo "[$(date -u +%FT%TZ)] ERROR: Backup file is corrupt" >&2
    exit 1
fi

# ── Upload to S3 (optional) ──────────────────────────────────────────────────
if [[ -n "${S3_BUCKET}" ]]; then
    echo "[$(date -u +%FT%TZ)] Uploading to s3://${S3_BUCKET}/mysql/${FILENAME}"
    aws s3 cp "${FILEPATH}" "s3://${S3_BUCKET}/mysql/${FILENAME}" --storage-class STANDARD_IA
    echo "[$(date -u +%FT%TZ)] Upload complete"
fi

# ── Prune old backups ────────────────────────────────────────────────────────
echo "[$(date -u +%FT%TZ)] Removing backups older than ${RETENTION_DAYS} days"
find "${BACKUP_DIR}" -name "meshphoto_*.sql.gz" -mtime "+${RETENTION_DAYS}" -delete

echo "[$(date -u +%FT%TZ)] Backup finished successfully"
