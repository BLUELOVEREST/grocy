CREATE TABLE eric_food_sources (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	product_id INTEGER NOT NULL,
	provider TEXT NOT NULL,
	external_id TEXT NOT NULL,
	category TEXT,
	source_payload TEXT,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
	UNIQUE(provider, external_id),
	FOREIGN KEY(product_id) REFERENCES products(id)
);

CREATE INDEX ix_eric_food_sources_product_id
ON eric_food_sources(product_id);

CREATE TRIGGER cascade_eric_food_sources_removal AFTER DELETE ON products
BEGIN
	DELETE FROM eric_food_sources
	WHERE product_id = OLD.id;
END;
