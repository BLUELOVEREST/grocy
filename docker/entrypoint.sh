#!/bin/sh
set -eu

mkdir -p "${GROCY_DATAPATH}" "${GROCY_DATAPATH}/viewcache" "${GROCY_DATAPATH}/settingoverrides" "${GROCY_DATAPATH}/storage"

if [ ! -f "${GROCY_DATAPATH}/config.php" ]; then
	cat > "${GROCY_DATAPATH}/config.php" <<'EOF'
<?php
// Runtime configuration is provided by config-dist.php defaults and GROCY_* environment variables.
EOF
fi

# Run Grocy's idempotent migration service before Apache handles any request.
php -r 'define("GROCY_DATAPATH", getenv("GROCY_DATAPATH")); require "/var/www/html/packages/autoload.php"; require GROCY_DATAPATH . "/config.php"; require "/var/www/html/config-dist.php"; Grocy\Services\DatabaseMigrationService::GetInstance()->MigrateDatabase();'

# Migrations run as root and can create the database on a fresh bind mount.
chown -R www-data:www-data "${GROCY_DATAPATH}"

exec "$@"
