# SparkyFitness Nutrition Field Alignment Design

Date: 2026-08-06

## Goal

Align Grocy's custom food nutrition model with SparkyFitness' predefined nutrient fields so Grocy can become the authoritative searchable food library for SparkyFitness.

The change covers Grocy storage, Grocy web editing/display, Grocy food library APIs, external import mapping, recipe nutrition totals, and the SparkyFitness Grocy adapter. Existing production data is not a constraint for this iteration, but migrations should still be safe for local and test databases that already contain early nutrition data.

## Field Set

Grocy will store the same built-in nutrient fields SparkyFitness uses:

| Field | Unit | Notes |
| --- | --- | --- |
| `calories` | kcal | Existing Grocy custom field |
| `protein` | g | Existing Grocy custom field |
| `carbs` | g | Rename Grocy custom `carbohydrates` to `carbs` |
| `fat` | g | Existing Grocy custom field |
| `saturated_fat` | g | New |
| `polyunsaturated_fat` | g | New |
| `monounsaturated_fat` | g | New |
| `trans_fat` | g | New |
| `cholesterol` | mg | New |
| `sodium` | mg | New |
| `potassium` | mg | New |
| `dietary_fiber` | g | New |
| `sugars` | g | New |
| `vitamin_a` | microgram | New |
| `vitamin_c` | mg | New |
| `calcium` | mg | New |
| `iron` | mg | New |

All values are nullable and represent the amount per the product's configured nutrition basis amount/unit, for example per `100 g` or per `1 egg`.

## Database

Extend the existing `product_nutrition` table instead of introducing an extension table or JSON field.

Migration behavior:

- Rename `product_nutrition.carbohydrates` to `carbs` when the old column exists.
- Add the missing 13 nullable numeric columns.
- Leave existing values intact where possible.
- Use nullable columns rather than defaulting to `0`, because unknown nutrition data must not look like a measured zero.

The code should use `carbs` everywhere after this change. There will be no `carbohydrates` compatibility layer in Grocy APIs, internal PHP code, frontend JavaScript, or tests.

## Grocy Services And APIs

`ProductNutritionService` will save and return the complete nutrient set. Required validation remains limited to:

- `is_food`
- positive `basis_amount`
- valid `basis_qu_id`

Individual nutrient values are optional nullable floats.

`FoodLibraryService` will:

- Select all 17 nutrient columns.
- Return all 17 fields in `nutrition`.
- Accept all 17 fields during `ImportFood`.
- Include full nutrition in imported local results and external candidates.

`BooheeFoodSearchService` will continue requiring the current minimum fields needed to make a usable search candidate: calories, protein, fat, and carbs. If Boohee includes richer fields such as saturated fat or fiber, they should be mapped into the new fields. Missing optional fields remain `null`.

`ChinaFoodCompositionImportService` will not need to fully populate the expanded field set in this iteration. It should keep current imports working and map any already obvious fields only if they are present in the existing JSON source. A later pass can improve coverage against the Chinese food composition data.

## Recipe Nutrition

`RecipeNutritionService` will calculate totals and per-serving values for all 17 fields using the same unit conversion logic it already uses for the four existing fields.

Missing nutrient values should remain visible as warnings instead of silently becoming zero. This makes incomplete foods obvious while allowing partial data to improve over time.

## Grocy Web UI

Grocy product editing will expose all 17 fields in the existing Food nutrition area.

The fields should be grouped for scanability:

- Basis: nutrition basis amount/unit and stock-to-basis conversion.
- Core: calories, protein, carbs, fat.
- Fat details: saturated, polyunsaturated, monounsaturated, trans fat.
- Other nutrients: cholesterol, sodium, potassium, dietary fiber, sugars, vitamin A, vitamin C, calcium, iron.

Food library display/edit surfaces should show and preserve the complete nutrition object. Labels should include units in field labels or hints, while still making it clear values are per nutrition basis.

## SparkyFitness Integration

The SparkyFitness Grocy adapter will:

- Read Grocy `nutrition.carbs`.
- Map all 17 Grocy fields into each `FoodVariant`.
- Scale all 17 fields for converted serving variants.
- Send all available 17 fields when importing a SparkyFitness food into Grocy.

Because SparkyFitness variants allow optional micronutrient fields, null or missing Grocy values should map to the existing app-compatible absence/default behavior without filtering otherwise valid foods.

## Compatibility And Non-Goals

This iteration does not backfill rich nutrition data. Existing local foods may only have calories, protein, carbs, and fat.

This iteration does not add custom nutrients beyond SparkyFitness' predefined built-in set.

This iteration does not require changing Grocy's upstream product `calories` field or historical upstream calorie views. The custom food library nutrition model remains separate and basis-aware.

## Testing

Grocy tests should cover:

- Migration creates `carbs` and all expanded nutrient columns.
- Saving product nutrition persists all 17 fields.
- Food library search returns all 17 fields.
- Import accepts all 17 fields.
- Boohee mapping preserves optional fields when present and leaves missing optional fields null.
- Recipe nutrition totals include all 17 fields.

SparkyFitness tests should cover:

- Grocy adapter maps `carbs`.
- Converted variants scale all 17 fields.
- Import to Grocy sends all 17 fields.
- Grocy foods are not filtered out when optional micronutrients are missing.
