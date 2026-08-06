# SparkyFitness Nutrition Field Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Expand Grocy's custom product nutrition model to the full SparkyFitness nutrient field set and use `carbs` everywhere instead of `carbohydrates`.

**Architecture:** Extend `product_nutrition` with nullable scalar columns and centralize the nutrient key list in Grocy services so saving, food-library responses, imports, and recipe totals stay aligned. Update SparkyFitness' Grocy adapter to consume and produce the same `carbs`-based contract.

**Tech Stack:** Grocy PHP services, SQLite migrations, Blade/jQuery views, Eric PHP contract tests, SparkyFitness TypeScript services and Vitest tests.

---

### Task 1: Grocy Data And Service Contract

**Files:**
- Create: `migrations/0263.sql`
- Modify: `services/ProductNutritionService.php`
- Modify: `services/RecipeNutritionService.php`
- Test: `tests/eric/product_nutrition_expanded_fields_test.php`

- [ ] Write a failing PHP contract test that saves all 17 fields and verifies `carbs` is persisted.
- [ ] Add migration `0263.sql` to rename `carbohydrates` to `carbs` and add the remaining nullable numeric fields.
- [ ] Update `ProductNutritionService` and `RecipeNutritionService` to use the 17-field list.
- [ ] Run `php tests/eric/product_nutrition_expanded_fields_test.php`.

### Task 2: Grocy Food Library Contract

**Files:**
- Modify: `services/FoodLibraryService.php`
- Modify: `services/BooheeFoodSearchService.php`
- Modify: `services/ChinaFoodCompositionImportService.php`
- Test: `tests/eric/food_library_fallback_contract_test.php`
- Test: `tests/eric/boohee_food_search_service_mapper_test.php`
- Test: `tests/eric/import_from_source_contract_test.php`

- [ ] Update tests to expect `nutrition.carbs` and optional expanded fields.
- [ ] Update SQL selects and row mapping to return all 17 fields.
- [ ] Update imports to accept all 17 fields.
- [ ] Update Boohee mapping to emit `carbs`, `saturated_fat`, and `dietary_fiber` when available.
- [ ] Run the affected Eric PHP tests.

### Task 3: Grocy Web UI

**Files:**
- Modify: `views/productform.blade.php`
- Modify: `public/viewjs/productform.js`
- Modify: `views/foodlibrary.blade.php`
- Modify: `public/viewjs/foodlibrary.js`
- Test: `tests/eric/foodlibrary_external_candidate_ui_test.php`

- [ ] Replace UI field IDs and payload keys from `carbohydrates` to `carbs`.
- [ ] Add inputs/display rows for the 13 new fields, grouped by nutrient type.
- [ ] Ensure food-library import/edit payload preserves all 17 fields.
- [ ] Run the affected Eric UI contract test.

### Task 4: SparkyFitness Adapter

**Files:**
- Modify: `/home/zhangzhicheng/workspace/own/SparkyFitness/SparkyFitnessServer/integrations/grocy/grocyFoodService.ts`
- Test: `/home/zhangzhicheng/workspace/own/SparkyFitness/SparkyFitnessServer/tests/grocyFoodService.test.ts`

- [ ] Update the Grocy nutrition type to `carbs` plus expanded optional fields.
- [ ] Scale all 17 fields in Grocy variants.
- [ ] Send all 17 fields when importing a SparkyFitness food into Grocy.
- [ ] Run `pnpm exec vitest run tests/grocyFoodService.test.ts`.

### Task 5: Verification

**Files:**
- All touched files.

- [ ] Run `php tests/eric/*.php` in Grocy.
- [ ] Run `bash tests/eric/*.sh` in Grocy.
- [ ] Run SparkyFitness server and frontend typechecks if SparkyFitness files changed.
- [ ] Run `git diff --check` in both repos.
