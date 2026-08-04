CREATE TABLE eric_food_aliases (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	product_id INTEGER NOT NULL,
	alias TEXT NOT NULL,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
	UNIQUE(product_id, alias),
	FOREIGN KEY(product_id) REFERENCES products(id)
);

CREATE INDEX ix_eric_food_aliases_product_id
ON eric_food_aliases(product_id);

CREATE INDEX ix_eric_food_aliases_alias
ON eric_food_aliases(alias);

CREATE TRIGGER cascade_eric_food_aliases_removal AFTER DELETE ON products
BEGIN
	DELETE FROM eric_food_aliases
	WHERE product_id = OLD.id;
END;
