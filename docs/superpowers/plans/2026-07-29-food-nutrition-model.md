# Food Nutrition Model Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace ambiguous per-stock-unit food macro fields with a product nutrition model that stores nutrition facts by explicit basis unit and supports product-specific nutrition conversion.

**Architecture:** Keep `products.is_food` as the food marker, add `product_nutrition` for structured nutrition facts, and expose custom API endpoints to load/save food nutrition semantics. Product UI will use these endpoints while underlying conversion storage reuses Grocy product-specific `quantity_unit_conversions`.

**Tech Stack:** Grocy PHP/Slim controllers, NotORM database access, Blade views, jQuery product form JavaScript, SQLite migrations.

---

## File Map

- Create: `migrations/0259.sql` for `product_nutrition` and cleanup triggers.
- Create: `services/ProductNutritionService.php` for nutrition load/save and conversion upsert logic.
- Create: `controllers/Api/FoodNutritionApiController.php` for custom API endpoints.
- Modify: `routes.php` to register `GET/PUT /api/food-nutrition/{productId}`.
- Modify: `views/productform.blade.php` to replace direct macro fields with semantic food nutrition section.
- Modify: `public/viewjs/productform.js` to load/save nutrition via custom API and refresh nutrition conversion UI.
- Optional modify: `eric-version.json` only when implementation is verified and ready to tag.

---

### Task 1: Database Migration

**Files:**
- Create: `migrations/0259.sql`

- [ ] **Step 1: Write the migration smoke test command**

Run this before creating the migration. Expected result: `migrations/0259.sql` is missing and command exits non-zero.

```bash
test -f migrations/0259.sql && rg -n "product_nutrition|nutrition_basis|basis_qu_id" migrations/0259.sql
```

- [ ] **Step 2: Create `migrations/0259.sql`**

```sql
CREATE TABLE product_nutrition (
	product_id INTEGER NOT NULL PRIMARY KEY,
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
```

- [ ] **Step 3: Run SQLite migration verification**

```bash
tmpdb=$(mktemp /tmp/grocy-food-nutrition.XXXXXX.sqlite)
sqlite3 "$tmpdb" <<'SQL'
CREATE TABLE products (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, name TEXT NOT NULL UNIQUE);
CREATE TABLE quantity_units (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, name TEXT NOT NULL UNIQUE);
.read migrations/0259.sql
INSERT INTO products (id, name) VALUES (1, '鸡蛋');
INSERT INTO quantity_units (id, name) VALUES (1, 'g');
INSERT INTO product_nutrition (product_id, basis_amount, basis_qu_id, calories, protein, fat, carbohydrates)
VALUES (1, 100, 1, 143, 13, 10, 1);
SELECT product_id, basis_amount, basis_qu_id, calories, protein, fat, carbohydrates FROM product_nutrition;
DELETE FROM products WHERE id = 1;
SELECT COUNT(*) FROM product_nutrition;
SQL
rm "$tmpdb"
```

Expected output:

```text
1|100.0|1|143.0|13.0|10.0|1.0
0
```

- [ ] **Step 4: Commit database migration**

```bash
git add migrations/0259.sql
git commit -m "feat: add product nutrition table"
```

---

### Task 2: Product Nutrition Service

**Files:**
- Create: `services/ProductNutritionService.php`

- [ ] **Step 1: Write the failing syntax/existence check**

Run before creating the service. Expected result: file missing or `ProductNutritionService` not found.

```bash
test -f services/ProductNutritionService.php && php -l services/ProductNutritionService.php && rg -n "class ProductNutritionService|SaveNutrition|GetNutrition" services/ProductNutritionService.php
```

- [ ] **Step 2: Implement `ProductNutritionService.php`**

Create a service with these public methods:

```php
<?php

namespace Grocy\Services;

class ProductNutritionService extends BaseService
{
	public function GetNutrition($productId)
	{
		$product = $this->DB->products($productId);
		if ($product === null)
		{
			throw new \InvalidArgumentException('Product not found');
		}

		$nutrition = $this->DB->product_nutrition()->where('product_id', $productId)->fetch();
		$stockToBasisConversion = null;

		if ($nutrition !== null)
		{
			$stockToBasisConversion = $this->DB->quantity_unit_conversions()->where('product_id = :1 AND from_qu_id = :2 AND to_qu_id = :3', $productId, $product->qu_id_stock, $nutrition->basis_qu_id)->fetch();
		}

		return [
			'is_food' => (int)$product->is_food === 1,
			'nutrition' => $nutrition,
			'stock_to_basis_conversion' => $stockToBasisConversion
		];
	}

	public function SaveNutrition($productId, array $payload)
	{
		$product = $this->DB->products($productId);
		if ($product === null)
		{
			throw new \InvalidArgumentException('Product not found');
		}

		$isFood = !empty($payload['is_food']) ? 1 : 0;
		$product->update(['is_food' => $isFood]);

		if ($isFood === 0)
		{
			return $this->GetNutrition($productId);
		}

		$basisAmount = (float)($payload['basis_amount'] ?? 100);
		$basisQuId = (int)($payload['basis_qu_id'] ?? 0);
		if ($basisAmount <= 0 || $basisQuId <= 0)
		{
			throw new \InvalidArgumentException('A positive nutrition basis amount and quantity unit are required');
		}

		$values = [
			'product_id' => $productId,
			'basis_amount' => $basisAmount,
			'basis_qu_id' => $basisQuId,
			'calories' => $this->NullableFloat($payload, 'calories'),
			'protein' => $this->NullableFloat($payload, 'protein'),
			'fat' => $this->NullableFloat($payload, 'fat'),
			'carbohydrates' => $this->NullableFloat($payload, 'carbohydrates')
		];

		$existing = $this->DB->product_nutrition()->where('product_id', $productId)->fetch();
		if ($existing === null)
		{
			$this->DB->product_nutrition()->createRow($values)->save();
		}
		else
		{
			$existing->update($values);
		}

		$this->SaveStockToBasisConversion($productId, (int)$product->qu_id_stock, $basisQuId, $payload);

		return $this->GetNutrition($productId);
	}

	private function NullableFloat(array $payload, string $key)
	{
		if (!array_key_exists($key, $payload) || $payload[$key] === '' || $payload[$key] === null)
		{
			return null;
		}

		return (float)$payload[$key];
	}

	private function SaveStockToBasisConversion($productId, $stockQuId, $basisQuId, array $payload)
	{
		if ($stockQuId === $basisQuId || !array_key_exists('stock_to_basis_factor', $payload) || $payload['stock_to_basis_factor'] === '' || $payload['stock_to_basis_factor'] === null)
		{
			return;
		}

		$factor = (float)$payload['stock_to_basis_factor'];
		if ($factor <= 0)
		{
			throw new \InvalidArgumentException('Stock to nutrition basis conversion factor must be positive');
		}

		$existing = $this->DB->quantity_unit_conversions()->where('product_id = :1 AND from_qu_id = :2 AND to_qu_id = :3', $productId, $stockQuId, $basisQuId)->fetch();
		$values = [
			'product_id' => $productId,
			'from_qu_id' => $stockQuId,
			'to_qu_id' => $basisQuId,
			'factor' => $factor
		];

		if ($existing === null)
		{
			$this->DB->quantity_unit_conversions()->createRow($values)->save();
		}
		else
		{
			$existing->update($values);
		}
	}
}
```

- [ ] **Step 3: Run syntax check**

```bash
php -l services/ProductNutritionService.php
```

Expected output:

```text
No syntax errors detected in services/ProductNutritionService.php
```

- [ ] **Step 4: Commit service**

```bash
git add services/ProductNutritionService.php
git commit -m "feat: add product nutrition service"
```

---

### Task 3: Food Nutrition API

**Files:**
- Create: `controllers/Api/FoodNutritionApiController.php`
- Modify: `routes.php`

- [ ] **Step 1: Write the failing route check**

```bash
rg -n "food-nutrition|FoodNutritionApiController" controllers/Api routes.php
```

Expected: no matches.

- [ ] **Step 2: Add `FoodNutritionApiController.php`**

Create a controller that reads JSON request bodies, calls `ProductNutritionService`, returns JSON, and maps invalid input to HTTP 400.

```php
<?php

namespace Grocy\Controllers\Api;

use Grocy\Services\ProductNutritionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FoodNutritionApiController extends BaseApiController
{
	public function Get(Request $request, Response $response, array $args)
	{
		return $this->ApiResponse($response, ProductNutritionService::GetInstance()->GetNutrition($args['productId']));
	}

	public function Put(Request $request, Response $response, array $args)
	{
		$payload = $request->getParsedBody();
		if (!is_array($payload))
		{
			$payload = json_decode($request->getBody()->getContents(), true) ?: [];
		}

		try
		{
			return $this->ApiResponse($response, ProductNutritionService::GetInstance()->SaveNutrition($args['productId'], $payload));
		}
		catch (\InvalidArgumentException $ex)
		{
			$response->getBody()->write(json_encode(['error_message' => $ex->getMessage()]));
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
		}
	}
}
```

- [ ] **Step 3: Register routes in `routes.php`**

Add routes next to the other API routes:

```php
$app->get('/api/food-nutrition/{productId}', 'Grocy\Controllers\Api\FoodNutritionApiController:Get');
$app->put('/api/food-nutrition/{productId}', 'Grocy\Controllers\Api\FoodNutritionApiController:Put');
```

- [ ] **Step 4: Run syntax checks**

```bash
php -l controllers/Api/FoodNutritionApiController.php
php -l routes.php
```

Expected both files report no syntax errors.

- [ ] **Step 5: Commit API**

```bash
git add controllers/Api/FoodNutritionApiController.php routes.php
git commit -m "feat: expose food nutrition api"
```

---

### Task 4: Product Form UI And Save Flow

**Files:**
- Modify: `views/productform.blade.php`
- Modify: `public/viewjs/productform.js`

- [ ] **Step 1: Write the failing UI check**

```bash
rg -n "nutrition_basis|food-nutrition|stock_to_basis_factor|product_nutrition" views/productform.blade.php public/viewjs/productform.js
```

Expected: no complete implementation exists.

- [ ] **Step 2: Replace macro fields in `views/productform.blade.php`**

Change the `Food product` section so it includes:

```text
Nutrition basis amount numberpicker: id `nutrition_basis_amount`
Nutrition basis quantity unit select: id `nutrition_basis_qu_id`
Calories numberpicker: id `nutrition_calories`
Protein numberpicker: id `nutrition_protein`
Fat numberpicker: id `nutrition_fat`
Carbohydrates numberpicker: id `nutrition_carbohydrates`
Stock-to-basis conversion numberpicker: id `stock_to_basis_factor`
```

Keep the existing `calories` field out of the visible nutrition section or make it hidden for Grocy compatibility. Do not keep visible `protein`, `fat`, or `carbohydrates` inputs that submit directly to `products`.

- [ ] **Step 3: Update `productform.js` save flow**

After the product itself is created/updated and before redirect, call:

```javascript
Grocy.Api.Put('food-nutrition/' + productId, {
	is_food: $('#is_food').prop('checked'),
	basis_amount: $('#nutrition_basis_amount').val(),
	basis_qu_id: $('#nutrition_basis_qu_id').val(),
	calories: $('#nutrition_calories').val(),
	protein: $('#nutrition_protein').val(),
	fat: $('#nutrition_fat').val(),
	carbohydrates: $('#nutrition_carbohydrates').val(),
	stock_to_basis_factor: $('#stock_to_basis_factor').val()
}, success, error);
```

When saving `objects/products`, remove direct `protein`, `fat`, and `carbohydrates` from `jsonData` so deprecated product columns are not used by the new UI.

- [ ] **Step 4: Update `productform.js` load flow**

On edit mode, load:

```javascript
Grocy.Api.Get('food-nutrition/' + Grocy.EditObjectId, function(result) {
	$('#is_food').prop('checked', BoolVal(result.is_food));
	if (result.nutrition != null) {
		$('#nutrition_basis_amount').val(result.nutrition.basis_amount);
		$('#nutrition_basis_qu_id').val(result.nutrition.basis_qu_id);
		$('#nutrition_calories').val(result.nutrition.calories);
		$('#nutrition_protein').val(result.nutrition.protein);
		$('#nutrition_fat').val(result.nutrition.fat);
		$('#nutrition_carbohydrates').val(result.nutrition.carbohydrates);
	}
	if (result.stock_to_basis_conversion != null) {
		$('#stock_to_basis_factor').val(result.stock_to_basis_conversion.factor);
	}
	refreshNutritionFieldsVisibility();
});
```

- [ ] **Step 5: Add conversion visibility logic**

Show the conversion shortcut only when all are true:

```text
Food product checked
Stock unit selected
Nutrition basis unit selected
Stock unit differs from nutrition basis unit
```

- [ ] **Step 6: Run syntax checks**

```bash
php -l views/productform.blade.php
node --check public/viewjs/productform.js
git diff --check
```

Expected: all commands exit 0.

- [ ] **Step 7: Commit UI**

```bash
git add views/productform.blade.php public/viewjs/productform.js
git commit -m "feat: edit food nutrition on product form"
```

---

### Task 5: Container Smoke Test

**Files:**
- No source changes unless a defect is found.

- [ ] **Step 1: Build or use current local image**

Use the existing Docker build workflow or local image command used by this repository. The test container should expose port `9285` and map test data to `/home/zhangzhicheng/workspace/own/eric-grocy-test/data:/var/www/html/data`.

- [ ] **Step 2: Run migration/API smoke checks**

Use the test API key from the test instance and run:

```bash
/usr/bin/curl -sS -H 'GROCY-API-KEY: <key>' http://127.0.0.1:9285/api/food-nutrition/1 | jq '.'
/usr/bin/curl -sS -X PUT -H 'Content-Type: application/json' -H 'GROCY-API-KEY: <key>' \
  -d '{"is_food":true,"basis_amount":100,"basis_qu_id":<g-unit-id>,"calories":143,"protein":13,"fat":10,"carbohydrates":1,"stock_to_basis_factor":50}' \
  http://127.0.0.1:9285/api/food-nutrition/1 | jq '.'
```

Expected response includes:

```json
{
  "is_food": true,
  "nutrition": {
    "product_id": 1,
    "basis_amount": 100,
    "basis_qu_id": "<g-unit-id>",
    "calories": 143,
    "protein": 13,
    "fat": 10,
    "carbohydrates": 1
  },
  "stock_to_basis_conversion": {
    "product_id": 1,
    "factor": 50
  }
}
```

- [ ] **Step 3: Verify database rows**

```bash
sqlite3 /home/zhangzhicheng/workspace/own/eric-grocy-test/data/grocy.db \
  "SELECT product_id, basis_amount, basis_qu_id, calories, protein, fat, carbohydrates FROM product_nutrition WHERE product_id = 1; SELECT product_id, from_qu_id, to_qu_id, factor FROM quantity_unit_conversions WHERE product_id = 1;"
```

Expected: one nutrition row and one product-specific conversion from stock unit to nutrition basis unit.

- [ ] **Step 4: Manual web test**

Open:

```text
http://192.168.200.101:9285/product/1
```

Verify:

```text
Food product checked
Nutrition basis shows 100 g
Nutrition values show 143 / 13 / 10 / 1
Nutrition conversion shows 1 件 = 50 g
Save and reopen preserves values
```

---

### Task 6: Version Bump, Tag, And Push

**Files:**
- Modify: `eric-version.json`

- [ ] **Step 1: Bump custom version**

Update `eric-version.json` from `4.6.0-eric.4` to `4.6.0-eric.5`:

```json
{
	"base_version": "4.6.0",
	"base_release_date": "2026-03-06",
	"custom_revision": 5,
	"version": "4.6.0-eric.5"
}
```

- [ ] **Step 2: Run final verification**

```bash
php -l services/ProductNutritionService.php
php -l controllers/Api/FoodNutritionApiController.php
php -l routes.php
php -l views/productform.blade.php
node --check public/viewjs/productform.js
git diff --check
```

Expected: all commands exit 0.

- [ ] **Step 3: Commit version bump**

```bash
git add eric-version.json
git commit -m "chore: bump custom grocy version to 4.6.0-eric.5"
```

- [ ] **Step 4: Tag and push**

```bash
git tag v4.6.0-eric.5
git push gitea eric/custom-shopping-flow
git push gitea v4.6.0-eric.5
```

- [ ] **Step 5: Confirm remote tag**

```bash
git ls-remote --tags gitea v4.6.0-eric.5
```

Expected output includes `refs/tags/v4.6.0-eric.5`.

---

## Self-Review

- Spec coverage: The plan covers `product_nutrition`, semantic API endpoints, product form load/save, product-specific conversion shortcut, test container verification, and version tagging.
- Scope control: Recipe totals, meal plan totals, barcode nutrition import, daily intake, and extra nutrients are intentionally excluded.
- Placeholder scan: No implementation steps depend on unspecified files or undefined behavior; runtime API key and unit ids are intentionally provided at smoke-test time because they come from the running test instance.
- Type consistency: Field names are consistent across migration, service, API payload, UI ids, and verification SQL.
