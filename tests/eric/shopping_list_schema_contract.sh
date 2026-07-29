#!/usr/bin/env sh

set -eu

SQLITE3_BIN="${SQLITE3_BIN:-sqlite3}"
DB_FILE="$(mktemp)"
trap 'rm -f "$DB_FILE"' EXIT

"$SQLITE3_BIN" "$DB_FILE" 'CREATE TABLE shopping_list (id INTEGER PRIMARY KEY AUTOINCREMENT);'
"$SQLITE3_BIN" "$DB_FILE" < migrations/0062.sql

list_count="$($SQLITE3_BIN "$DB_FILE" 'SELECT COUNT(*) FROM shopping_lists;')"
if [ "$list_count" -ne 0 ]; then
	echo "Expected zero shopping lists, got $list_count" >&2
	exit 1
fi

"$SQLITE3_BIN" "$DB_FILE" < migrations/0260.sql

shopping_list_not_null="$($SQLITE3_BIN "$DB_FILE" "SELECT \"notnull\" FROM pragma_table_info('shopping_list') WHERE name = 'shopping_list_id';")"
if [ "$shopping_list_not_null" -ne 1 ]; then
	echo 'shopping_list.shopping_list_id must be NOT NULL' >&2
	exit 1
fi

due_date_count="$($SQLITE3_BIN "$DB_FILE" "SELECT COUNT(*) FROM pragma_table_info('shopping_list') WHERE name = 'due_date';")"
if [ "$due_date_count" -ne 1 ]; then
	echo 'shopping_list.due_date is missing' >&2
	exit 1
fi

grep -Fq 'DatabaseMigrationService::GetInstance()->MigrateDatabase()' docker/entrypoint.sh

echo 'shopping list schema contract passed'
