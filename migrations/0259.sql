CREATE TABLE product_nutrition (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	product_id INTEGER NOT NULL UNIQUE,
	basis_amount REAL NOT NULL DEFAULT 100,
	basis_qu_id INTEGER NOT NULL,
	calories REAL,
	protein REAL,
	fat REAL,
	carbohydrates REAL,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
	FOREIGN KEY(product_id) REFERENCES products(id),
	FOREIGN KEY(basis_qu_id) REFERENCES quantity_units(id)
);

CREATE TRIGGER cascade_product_nutrition_removal AFTER DELETE ON products
BEGIN
	DELETE FROM product_nutrition
	WHERE product_id = OLD.id;
END;
