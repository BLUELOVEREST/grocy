ALTER TABLE shopping_list
ADD free_text_name TEXT;

ALTER TABLE shopping_list
ADD completion_type TEXT;

ALTER TABLE shopping_list
ADD stock_transaction_id TEXT;

ALTER TABLE shopping_list
ADD completed_timestamp DATETIME;
