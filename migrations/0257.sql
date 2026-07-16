CREATE TABLE product_property_definitions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	parent_product_id INTEGER NOT NULL,
	name TEXT NOT NULL,
	label TEXT NOT NULL,
	type TEXT NOT NULL DEFAULT 'text' CHECK(type IN ('text', 'number', 'select', 'checkbox')),
	unit TEXT,
	options TEXT,
	input_required TINYINT NOT NULL DEFAULT 0 CHECK(input_required IN (0, 1)),
	sort_number INTEGER,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
	UNIQUE(parent_product_id, name)
);

CREATE INDEX ix_product_property_definitions_parent_product_id ON product_property_definitions(parent_product_id);

CREATE TABLE product_property_values (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	product_id INTEGER NOT NULL,
	property_definition_id INTEGER NOT NULL,
	value TEXT,
	row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime')),
	UNIQUE(product_id, property_definition_id)
);

CREATE INDEX ix_product_property_values_product_id ON product_property_values(product_id);
CREATE INDEX ix_product_property_values_property_definition_id ON product_property_values(property_definition_id);

CREATE TRIGGER cascade_product_property_definition_removal AFTER DELETE ON product_property_definitions
BEGIN
	DELETE FROM product_property_values
	WHERE property_definition_id = OLD.id;
END;

CREATE TRIGGER cascade_product_property_product_removal AFTER DELETE ON products
BEGIN
	DELETE FROM product_property_values
	WHERE product_id = OLD.id;

	DELETE FROM product_property_definitions
	WHERE parent_product_id = OLD.id;
END;
