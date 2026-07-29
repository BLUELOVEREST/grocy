CREATE TABLE shopping_lists (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL UNIQUE,
	description TEXT,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
);

ALTER TABLE shopping_list
ADD shopping_list_id INTEGER NOT NULL REFERENCES shopping_lists(id);
