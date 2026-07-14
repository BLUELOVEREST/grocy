#!/bin/sh
set -eu

mkdir -p "${GROCY_DATAPATH}" "${GROCY_DATAPATH}/viewcache" "${GROCY_DATAPATH}/settingoverrides" "${GROCY_DATAPATH}/storage"

if [ ! -f "${GROCY_DATAPATH}/config.php" ]; then
	cat > "${GROCY_DATAPATH}/config.php" <<'EOF'
<?php
// Runtime configuration is provided by config-dist.php defaults and GROCY_* environment variables.
EOF
fi

chown -R www-data:www-data "${GROCY_DATAPATH}"

exec "$@"
