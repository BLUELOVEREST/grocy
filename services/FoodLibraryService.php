<?php

namespace Grocy\Services;

class FoodLibraryService extends BaseService
{
	public function SearchFoods($query = null, $page = 1, $pageSize = 20)
	{
		$page = max(1, (int)$page);
		$pageSize = min(100, max(1, (int)$pageSize));
		$offset = ($page - 1) * $pageSize;
		$params = [];
		$where = 'p.active = 1 AND p.is_food = 1';

		if ($query !== null && trim($query) !== '')
		{
			$where .= ' AND p.name LIKE :query';
			$params[':query'] = '%' . trim($query) . '%';
		}

		$pdo = DatabaseService::GetInstance()->GetDbConnectionRaw();
		$countStatement = $pdo->prepare('SELECT COUNT(DISTINCT p.id) FROM products p WHERE ' . $where);
		foreach ($params as $key => $value)
		{
			$countStatement->bindValue($key, $value);
		}
		$countStatement->execute();
		$totalCount = (int)$countStatement->fetchColumn();

		$sql = '
			SELECT
				p.*,
				pn.basis_amount,
				pn.basis_qu_id,
				pn.calories AS nutrition_calories,
				pn.protein AS nutrition_protein,
				pn.fat AS nutrition_fat,
				pn.carbohydrates AS nutrition_carbohydrates,
				qu_stock.name AS stock_unit_name,
				qu_stock.name_plural AS stock_unit_name_plural,
				qu_basis.name AS basis_unit_name,
				qu_basis.name_plural AS basis_unit_name_plural,
				efs.provider,
				efs.external_id,
				efs.category,
				efs.source_payload
			FROM products p
			LEFT JOIN product_nutrition pn
				ON p.id = pn.product_id
			LEFT JOIN quantity_units qu_stock
				ON p.qu_id_stock = qu_stock.id
			LEFT JOIN quantity_units qu_basis
				ON pn.basis_qu_id = qu_basis.id
			LEFT JOIN eric_food_sources efs
				ON efs.id = (SELECT MIN(id) FROM eric_food_sources WHERE product_id = p.id)
			WHERE ' . $where . '
			ORDER BY p.name COLLATE NOCASE
			LIMIT :limit OFFSET :offset';
		$statement = $pdo->prepare($sql);
		foreach ($params as $key => $value)
		{
			$statement->bindValue($key, $value);
		}
		$statement->bindValue(':limit', $pageSize, \PDO::PARAM_INT);
		$statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
		$statement->execute();

		return [
			'foods' => array_map([$this, 'MapFoodRow'], $statement->fetchAll(\PDO::FETCH_OBJ)),
			'pagination' => [
				'page' => $page,
				'pageSize' => $pageSize,
				'totalCount' => $totalCount,
				'hasMore' => $offset + $pageSize < $totalCount
			]
		];
	}

	public function GetFood($productId)
	{
		$pdo = DatabaseService::GetInstance()->GetDbConnectionRaw();
		$statement = $pdo->prepare('
			SELECT
				p.*,
				pn.basis_amount,
				pn.basis_qu_id,
				pn.calories AS nutrition_calories,
				pn.protein AS nutrition_protein,
				pn.fat AS nutrition_fat,
				pn.carbohydrates AS nutrition_carbohydrates,
				qu_stock.name AS stock_unit_name,
				qu_stock.name_plural AS stock_unit_name_plural,
				qu_basis.name AS basis_unit_name,
				qu_basis.name_plural AS basis_unit_name_plural,
				efs.provider,
				efs.external_id,
				efs.category,
				efs.source_payload
			FROM products p
			LEFT JOIN product_nutrition pn
				ON p.id = pn.product_id
			LEFT JOIN quantity_units qu_stock
				ON p.qu_id_stock = qu_stock.id
			LEFT JOIN quantity_units qu_basis
				ON pn.basis_qu_id = qu_basis.id
			LEFT JOIN eric_food_sources efs
				ON efs.id = (SELECT MIN(id) FROM eric_food_sources WHERE product_id = p.id)
			WHERE p.id = :productId
				AND p.active = 1
				AND p.is_food = 1');
		$statement->bindValue(':productId', (int)$productId, \PDO::PARAM_INT);
		$statement->execute();
		$row = $statement->fetch(\PDO::FETCH_OBJ);

		if ($row === false)
		{
			throw new \InvalidArgumentException('Food product not found');
		}

		$food = $this->MapFoodRow($row);
		$food['unit_conversions'] = $this->GetProductUnitConversions((int)$productId);

		return $food;
	}

	public function ImportFood(array $payload)
	{
		$provider = $this->RequiredPayloadValue($payload, 'provider');
		$externalId = $this->RequiredPayloadValue($payload, 'external_id');
		$name = $this->RequiredPayloadValue($payload, 'name');
		$stockUnitName = $payload['stock_unit'] ?? 'g';
		$stockUnitId = $this->ResolveQuantityUnitId($stockUnitName);
		$basisUnitId = $this->ResolveBasisQuantityUnitId($payload);
		$sourcePayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
		if ($sourcePayload === false)
		{
			throw new \InvalidArgumentException('Invalid source payload');
		}
		$category = $payload['category'] ?? null;
		$pdo = DatabaseService::GetInstance()->GetDbConnectionRaw();

		$pdo->beginTransaction();
		try
		{
			$source = $this->DB->eric_food_sources()->where('provider = :1 AND external_id = :2', $provider, $externalId)->fetch();
			if ($source === null)
			{
				$product = $this->DB->products()->createRow([
					'name' => $name,
					'active' => 1,
					'is_food' => 1,
					'location_id' => $this->ResolveDefaultLocationId(),
					'qu_id_purchase' => $stockUnitId,
					'qu_id_stock' => $stockUnitId,
					'qu_id_consume' => $stockUnitId,
					'qu_id_price' => $stockUnitId
				]);
				$product->save();

				$this->DB->eric_food_sources()->createRow([
					'product_id' => $product->id,
					'provider' => $provider,
					'external_id' => $externalId,
					'category' => $category,
					'source_payload' => $sourcePayload
				])->save();
			}
			else
			{
				$product = $this->DB->products($source->product_id);
				if ($product === null)
				{
					throw new \InvalidArgumentException('Food product not found');
				}

				$product->update([
					'name' => $name,
					'active' => 1,
					'is_food' => 1,
					'qu_id_purchase' => $stockUnitId,
					'qu_id_stock' => $stockUnitId
				]);
				$source->update([
					'category' => $category,
					'source_payload' => $sourcePayload
				]);
			}

			ProductNutritionService::GetInstance()->SaveNutritionInTransaction($product->id, $this->BuildNutritionPayload($payload, $basisUnitId));
			$pdo->commit();
		}
		catch (\Throwable $ex)
		{
			if ($pdo->inTransaction())
			{
				$pdo->rollback();
			}

			if ($this->IsDuplicateSourceException($ex))
			{
				$existingFood = $this->TryGetFoodBySource($provider, $externalId);
				if ($existingFood !== null)
				{
					return $existingFood;
				}
			}

			throw $ex;
		}

		return $this->GetFood($product->id);
	}

	private function ResolveQuantityUnitId($unitName)
	{
		$unitName = trim((string)$unitName);
		$unit = $this->DB->quantity_units()->where('name = :1 OR name_plural = :1', $unitName)->fetch();
		if ($unit === null)
		{
			throw new \InvalidArgumentException('Missing Grocy quantity unit: ' . $unitName);
		}

		return (int)$unit->id;
	}

	private function ResolveBasisQuantityUnitId(array $payload)
	{
		if (array_key_exists('basis_unit', $payload) && trim((string)$payload['basis_unit']) !== '')
		{
			return $this->ResolveQuantityUnitId($payload['basis_unit']);
		}

		if (array_key_exists('basis_qu_id', $payload))
		{
			$rawBasisQuId = $payload['basis_qu_id'];
			$basisQuId = filter_var($rawBasisQuId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
			if ($basisQuId === false || $this->DB->quantity_units($basisQuId) === null)
			{
				throw new \InvalidArgumentException('Missing Grocy quantity unit: ' . $rawBasisQuId);
			}

			return $basisQuId;
		}

		return $this->ResolveQuantityUnitId('g');
	}

	private function ResolveDefaultLocationId()
	{
		$location = $this->DB->locations()->where('active = 1')->order('id')->fetch();
		if ($location === null)
		{
			throw new \InvalidArgumentException('Missing Grocy location');
		}

		return (int)$location->id;
	}

	private function RequiredPayloadValue(array $payload, $key)
	{
		if (!array_key_exists($key, $payload) || trim((string)$payload[$key]) === '')
		{
			throw new \InvalidArgumentException($key . ' is required');
		}

		return trim((string)$payload[$key]);
	}

	private function BuildNutritionPayload(array $payload, $basisUnitId)
	{
		$nutritionPayload = [
			'is_food' => true,
			'basis_amount' => (float)($payload['basis_amount'] ?? 100),
			'basis_qu_id' => (int)$basisUnitId
		];

		foreach (['calories', 'protein', 'fat', 'carbohydrates', 'stock_to_basis_factor'] as $key)
		{
			if (array_key_exists($key, $payload))
			{
				$nutritionPayload[$key] = $payload[$key];
			}
		}

		return $nutritionPayload;
	}

	private function MapFoodRow($row)
	{
		return [
			'id' => (int)$row->id,
			'name' => $row->name,
			'description' => $row->description,
			'active' => (int)$row->active,
			'is_food' => (int)$row->is_food === 1,
			'stock_unit' => [
				'id' => (int)$row->qu_id_stock,
				'name' => $row->stock_unit_name,
				'name_plural' => $row->stock_unit_name_plural
			],
			'nutrition' => [
				'basis_amount' => $row->basis_amount === null ? null : (float)$row->basis_amount,
				'basis_qu_id' => $row->basis_qu_id === null ? null : (int)$row->basis_qu_id,
				'basis_unit' => [
					'id' => $row->basis_qu_id === null ? null : (int)$row->basis_qu_id,
					'name' => $row->basis_unit_name,
					'name_plural' => $row->basis_unit_name_plural
				],
				'calories' => $row->nutrition_calories === null ? null : (float)$row->nutrition_calories,
				'protein' => $row->nutrition_protein === null ? null : (float)$row->nutrition_protein,
				'fat' => $row->nutrition_fat === null ? null : (float)$row->nutrition_fat,
				'carbohydrates' => $row->nutrition_carbohydrates === null ? null : (float)$row->nutrition_carbohydrates
			],
			'source' => [
				'provider' => $row->provider,
				'external_id' => $row->external_id,
				'category' => $row->category,
				'source_payload' => $this->DecodeSourcePayload($row->source_payload)
			]
		];
	}

	private function GetProductUnitConversions($productId)
	{
		$statement = DatabaseService::GetInstance()->GetDbConnectionRaw()->prepare('
			SELECT
				quc.id,
				quc.from_qu_id,
				qu_from.name AS from_unit_name,
				qu_from.name_plural AS from_unit_name_plural,
				quc.to_qu_id,
				qu_to.name AS to_unit_name,
				qu_to.name_plural AS to_unit_name_plural,
				quc.factor
			FROM quantity_unit_conversions quc
			JOIN quantity_units qu_from
				ON quc.from_qu_id = qu_from.id
			JOIN quantity_units qu_to
				ON quc.to_qu_id = qu_to.id
			WHERE quc.product_id = :productId
			ORDER BY qu_from.name, qu_to.name');
		$statement->bindValue(':productId', $productId, \PDO::PARAM_INT);
		$statement->execute();

		return array_map(function ($row)
		{
			return [
				'id' => (int)$row->id,
				'from_qu_id' => (int)$row->from_qu_id,
				'from_unit' => [
					'id' => (int)$row->from_qu_id,
					'name' => $row->from_unit_name,
					'name_plural' => $row->from_unit_name_plural
				],
				'to_qu_id' => (int)$row->to_qu_id,
				'to_unit' => [
					'id' => (int)$row->to_qu_id,
					'name' => $row->to_unit_name,
					'name_plural' => $row->to_unit_name_plural
				],
				'factor' => (float)$row->factor
			];
		}, $statement->fetchAll(\PDO::FETCH_OBJ));
	}

	private function IsDuplicateSourceException(\Throwable $ex)
	{
		return strpos($ex->getMessage(), 'eric_food_sources.provider, eric_food_sources.external_id') !== false
			|| strpos($ex->getMessage(), 'UNIQUE constraint failed: eric_food_sources') !== false;
	}

	private function TryGetFoodBySource($provider, $externalId)
	{
		$source = $this->DB->eric_food_sources()->where('provider = :1 AND external_id = :2', $provider, $externalId)->fetch();
		if ($source === null)
		{
			return null;
		}

		try
		{
			return $this->GetFood($source->product_id);
		}
		catch (\InvalidArgumentException)
		{
			return null;
		}
	}

	private function DecodeSourcePayload($sourcePayload)
	{
		if ($sourcePayload === null || $sourcePayload === '')
		{
			return null;
		}

		$decoded = json_decode($sourcePayload, true);
		return json_last_error() === JSON_ERROR_NONE ? $decoded : $sourcePayload;
	}
}
