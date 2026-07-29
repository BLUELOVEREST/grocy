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

		$basisAmount = (float)($payload['basis_amount'] ?? 0);
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

		$values = [
			'product_id' => $productId,
			'from_qu_id' => $stockQuId,
			'to_qu_id' => $basisQuId,
			'factor' => $factor
		];

		$existing = $this->DB->quantity_unit_conversions()->where('product_id = :1 AND from_qu_id = :2 AND to_qu_id = :3', $productId, $stockQuId, $basisQuId)->fetch();
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
