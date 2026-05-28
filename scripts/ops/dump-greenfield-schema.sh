#!/usr/bin/env bash
set -euo pipefail

# Dumps schema after fresh migrate for greenfield installs (Plan_BaseDeDatos Fase 3 POC).
# Requires MySQL or default DB connection configured.

cd "$(dirname "$0")/../.."

echo "==> migrate:fresh (no seed)"
php artisan migrate:fresh --force --no-interaction

echo "==> schema:dump"
php artisan schema:dump --prune

echo "Greenfield schema dumped. Review database/schema/ before commit."
