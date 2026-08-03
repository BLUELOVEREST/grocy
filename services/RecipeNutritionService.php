<?php

namespace Grocy\Services;

class RecipeNutritionService extends BaseService
{
	public function GetRecipeNutrition($recipeId)
	{
		$recipe = $this->DB->recipes($recipeId);
		if ($recipe === null)
		{
			throw new \InvalidArgumentException('Recipe not found');
		}

		$total = $this->EmptyNutrition();
		$warnings = [];
		$recipePositions = $this->DB->recipes_pos_resolved()->where('recipe_id', $recipeId)->fetchAll();

		foreach ($recipePositions as $recipePosition)
		{
			$productId = $recipePosition->product_id_effective ?? $recipePosition->product_id;
			$nutrition = $this->DB->product_nutrition()->where('product_id', $productId)->fetch();

			if ($nutrition === null)
			{
				$this->AddWarning($warnings, $recipePosition, $productId, 'Missing food nutrition');
				continue;
			}

			$basisAmount = (float)$nutrition->basis_amount;
			if ($basisAmount <= 0)
			{
				$this->AddWarning($warnings, $recipePosition, $productId, 'Invalid food nutrition basis');
				continue;
			}

			$convertedAmount = $this->GetConvertedAmount($recipePosition, $productId, $nutrition, $warnings);
			if ($convertedAmount === null)
			{
				continue;
			}

			$factor = $convertedAmount / $basisAmount;
			foreach ($this->NutrientKeys() as $key)
			{
				$total[$key] += (float)($nutrition->{$key} ?? 0) * $factor;
			}
		}

		$perServing = null;
		$baseServings = (float)$recipe->base_servings;
		if ($baseServings > 0)
		{
			$perServing = $this->EmptyNutrition();
			foreach ($this->NutrientKeys() as $key)
			{
				$perServing[$key] = $total[$key] / $baseServings;
			}
		}

		return [
			'recipe_id' => (int)$recipeId,
			'total' => $total,
			'per_serving' => $perServing,
			'warnings' => $warnings,
			'complete' => count($warnings) === 0
		];
	}

	private function GetConvertedAmount($recipePosition, $productId, $nutrition, array &$warnings)
	{
		$ingredientQuId = (int)$recipePosition->qu_id;
		$basisQuId = (int)$nutrition->basis_qu_id;
		$amount = (float)$recipePosition->recipe_amount;

		if ($ingredientQuId === $basisQuId)
		{
			return $amount;
		}

		$conversion = $this->DB->cache__quantity_unit_conversions_resolved()->where('product_id = :1 AND from_qu_id = :2 AND to_qu_id = :3', $productId, $ingredientQuId, $basisQuId)->fetch();
		if ($conversion === null)
		{
			$this->AddWarning($warnings, $recipePosition, $productId, 'Missing quantity unit conversion');
			return null;
		}

		return $amount * (float)$conversion->factor;
	}

	private function AddWarning(array &$warnings, $recipePosition, $productId, $message)
	{
		$warnings[] = [
			'product_id' => (int)$productId,
			'recipe_pos_id' => isset($recipePosition->recipe_pos_id) ? (int)$recipePosition->recipe_pos_id : null,
			'qu_id' => isset($recipePosition->qu_id) ? (int)$recipePosition->qu_id : null,
			'message' => $message
		];
	}

	private function EmptyNutrition()
	{
		return [
			'calories' => 0.0,
			'protein' => 0.0,
			'fat' => 0.0,
			'carbohydrates' => 0.0
		];
	}

	private function NutrientKeys()
	{
		return ['calories', 'protein', 'fat', 'carbohydrates'];
	}
}
