<?php

// This is executed inside DatabaseMigrationService class/context

use Grocy\Services\DatabaseService;

$pdo = DatabaseService::GetInstance()->GetDbConnectionRaw();

$tableColumns = function (string $tableName) use ($pdo)
{
	$statement = $pdo->query('PRAGMA table_info(' . $tableName . ')');
	return array_map(function ($row)
	{
		return $row['name'];
	}, $statement->fetchAll(\PDO::FETCH_ASSOC));
};

$triggerExists = function (string $triggerName) use ($pdo)
{
	$statement = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'trigger' AND name = :name");
	$statement->execute([':name' => $triggerName]);
	return (int)$statement->fetchColumn() > 0;
};

// Official Grocy 4.7.1 uses migrations 0256/0257. Older Eric Grocy installations
// already used those migration numbers, so apply the official schema changes
// idempotently here when they were skipped by the numeric migration tracker.
$sessionColumns = $tableColumns('sessions');
if (!in_array('token_hash', $sessionColumns, true))
{
	$pdo->exec('DROP TABLE IF EXISTS sessions');
	$pdo->exec("
		CREATE TABLE sessions (
			id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
			user_id INTEGER NOT NULL,
			token_type TINYINT NOT NULL,
			token_hash TEXT NOT NULL UNIQUE,
			expires DATETIME NOT NULL,
			last_used DATETIME,
			client_info TEXT,
			row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
		)
	");
	$pdo->exec('
		CREATE INDEX ix_sessions_performance1 ON sessions (
			token_type,
			token_hash
		)
	');
}

$pdo->exec('
	UPDATE products
	SET parent_product_id = null
	WHERE id = parent_product_id
');

if (!$triggerExists('prevent_self_referenced_parent_product_INS'))
{
	$pdo->exec("
		CREATE TRIGGER prevent_self_referenced_parent_product_INS AFTER INSERT ON products
		BEGIN
		SELECT CASE WHEN((
			SELECT 1
			FROM products p
			WHERE p.id = NEW.id
				AND p.id = p.parent_product_id
			)
			NOTNULL) THEN RAISE(ABORT, 'A product cannot reference itself as a parent product') END;
		END
	");
}

if (!$triggerExists('prevent_self_referenced_parent_product_UPD'))
{
	$pdo->exec("
		CREATE TRIGGER prevent_self_referenced_parent_product_UPD AFTER UPDATE ON products
		BEGIN
			SELECT CASE WHEN((
				SELECT 1
				FROM products p
				WHERE p.id = NEW.id
					AND p.id = p.parent_product_id
			) NOTNULL) THEN RAISE(ABORT, 'A product cannot reference itself as a parent product') END;
		END
	");
}
