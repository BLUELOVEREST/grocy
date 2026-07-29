# Food Nutrition Model Design

## Goal

Add a clear food nutrition model to the Eric Grocy customization so food products can store nutrition facts and support future recipe nutrition calculation without mixing nutrition semantics into stock units.

## Current Problem

Grocy currently has one native product field, `products.calories`, which applies to every product and is interpreted per stock quantity unit. The first Eric customization added `products.is_food`, `products.protein`, `products.fat`, and `products.carbohydrates`, but those macro fields inherited the same ambiguous per-stock-unit meaning.

That model is not good enough for real food data. For example, eggs may be stocked as `piece`, while nutrition labels are usually written per `100 g`. Treating the stock unit as the nutrition basis makes values like `protein = 50` ambiguous or incorrect.

## Concepts

### Stock Unit

The stock unit remains Grocy's existing inventory unit.

Examples:

- Egg stock unit: `piece`
- Milk stock unit: `bottle`
- Rice stock unit: `kg`

This unit is for stock, purchase, consume, and inventory flows.

### Nutrition Basis

Nutrition facts are recorded exactly as the food label or source expresses them.

Examples:

- Per `100 g`
- Per `100 ml`
- Per `1 piece`
- Per `1 serving`

Nutrition fields are interpreted as values per nutrition basis, not per stock unit.

### Nutrition Conversion

Nutrition conversion maps recipe/stock units to the nutrition basis unit when they differ.

Examples:

- Egg: `1 piece = 50 g`
- Milk: `1 bottle = 250 ml`
- Protein bar: `1 bar = 60 g`

The underlying storage should reuse Grocy's product-specific `quantity_unit_conversions` when possible. The UI should present this as nutrition conversion inside the food nutrition section, not as a generic stock conversion.

## Data Model

Keep on `products`:

```text
is_food TINYINT NOT NULL DEFAULT 0
```

Add a new table:

```text
product_nutrition
  product_id INTEGER PRIMARY KEY
  basis_amount REAL NOT NULL DEFAULT 100
  basis_qu_id INTEGER NOT NULL
  calories REAL
  protein REAL
  fat REAL
  carbohydrates REAL
  row_created_timestamp DATETIME DEFAULT (datetime('now', 'localtime'))
```

The meaning is:

```text
For basis_amount basis_qu_id of this product, the food contains:
  calories kcal
  protein g
  fat g
  carbohydrates g
```

The earlier `products.protein`, `products.fat`, and `products.carbohydrates` columns should be treated as deprecated implementation artifacts after this model is added. New UI and calculation code must use `product_nutrition`.

`products.calories` remains for Grocy native compatibility. For food nutrition calculation, `product_nutrition.calories` is the source of truth. A product save can optionally mirror `product_nutrition.calories` into `products.calories` only when the nutrition basis matches the stock unit exactly; otherwise it should not pretend to be per-stock-unit data.

## Product UI

On the product edit page:

- Show `Food product` checkbox.
- When unchecked, hide the food nutrition section.
- When checked, show a `Food nutrition` section.

Food nutrition section fields:

```text
Nutrition basis:
[amount] [quantity unit]

Nutrition values:
Energy [kcal]
Protein [g]
Fat [g]
Carbohydrates [g]

Nutrition conversion, shown when stock unit and nutrition basis unit differ:
1 [stock unit] = [amount] [nutrition basis unit]
```

The conversion shortcut writes to Grocy product-specific `quantity_unit_conversions`:

```text
product_id = current product id
from_qu_id = stock unit id
to_qu_id = nutrition basis quantity unit id
factor = conversion amount
```

Example:

```text
Product: 鸡蛋
Stock unit: 件
Nutrition basis: 100 g
Protein: 13 g
Fat: 10 g
Carbohydrates: 1 g
Nutrition conversion: 1 件 = 50 g
```

## Recipe Nutrition Calculation

Future recipe nutrition calculation should use this flow:

1. Read recipe ingredient amount and unit.
2. Convert ingredient unit to the product's nutrition basis unit through `cache__quantity_unit_conversions_resolved`.
3. Calculate multiplier: `converted_amount / nutrition_basis_amount`.
4. Multiply calories, protein, fat, and carbohydrates by the multiplier.
5. Sum values at recipe level and serving level.

If conversion is missing:

- Preserve normal Grocy recipe behavior.
- Mark nutrition calculation for that ingredient as incomplete.
- Surface a warning in the recipe nutrition UI instead of failing the recipe.

## API Behavior

Use small custom API endpoints for nutrition because Grocy's generic object API can store rows but does not express the intended semantics.

Suggested endpoints:

```text
GET /api/food-nutrition/{productId}
PUT /api/food-nutrition/{productId}
```

`PUT` payload:

```json
{
  "is_food": true,
  "basis_amount": 100,
  "basis_qu_id": 8,
  "calories": 143,
  "protein": 13,
  "fat": 10,
  "carbohydrates": 1,
  "stock_to_basis_factor": 50
}
```

The endpoint should update `products.is_food`, upsert `product_nutrition`, and upsert the product-specific unit conversion when `stock_to_basis_factor` is present and the stock unit differs from the basis unit.

## Scope For Next Implementation

Implement now:

- Migration adding `product_nutrition`.
- Product nutrition service.
- Product nutrition API endpoints.
- Product edit page uses the new model.
- Product page saves and reloads food nutrition data.
- Product-specific nutrition conversion shortcut.

Do not implement yet:

- Full recipe nutrition totals UI.
- Meal plan nutrition totals.
- Barcode nutrition import.
- Daily intake tracking.
- Extra nutrients beyond calories, protein, fat, and carbohydrates.

## Testing Strategy

Use targeted checks because this Grocy fork has limited automated test coverage.

Required verification:

- PHP syntax check for new service, controller, route, and product form.
- JS syntax check for product form changes.
- SQLite migration test creating `product_nutrition`, upserting a row, and deleting a product.
- API smoke test with a test Grocy container:
  - create or use a food product
  - save nutrition basis and macros
  - save stock-to-basis conversion
  - reload via API and verify values
- Manual Web test:
  - edit product `鸡蛋`
  - set `Food product`
  - set nutrition basis `100 g`
  - set `1 件 = 50 g`
  - save and reopen
