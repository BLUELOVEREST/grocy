ALTER TABLE product_nutrition
RENAME COLUMN carbohydrates TO carbs;

ALTER TABLE product_nutrition
ADD saturated_fat REAL;

ALTER TABLE product_nutrition
ADD polyunsaturated_fat REAL;

ALTER TABLE product_nutrition
ADD monounsaturated_fat REAL;

ALTER TABLE product_nutrition
ADD trans_fat REAL;

ALTER TABLE product_nutrition
ADD cholesterol REAL;

ALTER TABLE product_nutrition
ADD sodium REAL;

ALTER TABLE product_nutrition
ADD potassium REAL;

ALTER TABLE product_nutrition
ADD dietary_fiber REAL;

ALTER TABLE product_nutrition
ADD sugars REAL;

ALTER TABLE product_nutrition
ADD vitamin_a REAL;

ALTER TABLE product_nutrition
ADD vitamin_c REAL;

ALTER TABLE product_nutrition
ADD calcium REAL;

ALTER TABLE product_nutrition
ADD iron REAL;
