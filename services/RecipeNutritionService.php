<?php

namespace Grocy\Services;

class RecipeNutritionService extends BaseService
{
	public function GetRecipeNutrition($recipeId)
	{
		$recipeId = filter_var($recipeId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
		if ($recipeId === false)
		{
			throw new \InvalidArgumentException('Invalid recipe id');
		}

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
				if ($nutrition->{$key} === null)
				{
					$this->AddWarning($warnings, $recipePosition, $productId, 'Missing nutrient value', $key);
					continue;
				}

				$total[$key] += (float)$nutrition->{$key} * $factor;
			}
		}

		$perServing = null;
		$perServingDenominator = null;
		$baseServings = (float)$recipe->base_servings;
		$desiredServings = (float)$recipe->desired_servings;
		if ($desiredServings > 0)
		{
			$perServingDenominator = 'desired_servings';
			$perServing = $this->EmptyNutrition();
			foreach ($this->NutrientKeys() as $key)
			{
				$perServing[$key] = $total[$key] / $desiredServings;
			}
		}
		else
		{
			$warnings[] = [
				'message' => 'Invalid recipe desired servings'
			];
		}

		return [
			'recipe_id' => $recipeId,
			'total' => $total,
			'per_serving' => $perServing,
			'servings' => [
				'base_servings' => $baseServings,
				'desired_servings' => $desiredServings,
				'per_serving_denominator' => $perServingDenominator
			],
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

	private function AddWarning(array &$warnings, $recipePosition, $productId, $message, $nutrient = null)
	{
		$warning = [
			'product_id' => (int)$productId,
			'product_id_effective' => isset($recipePosition->product_id_effective) ? (int)$recipePosition->product_id_effective : null,
			'original_product_id' => isset($recipePosition->product_id) ? (int)$recipePosition->product_id : null,
			'recipe_pos_id' => isset($recipePosition->recipe_pos_id) ? (int)$recipePosition->recipe_pos_id : null,
			'ingredient_group' => $recipePosition->ingredient_group ?? null,
			'qu_id' => isset($recipePosition->qu_id) ? (int)$recipePosition->qu_id : null,
			'message' => $message
		];

		if ($nutrient !== null)
		{
			$warning['nutrient'] = $nutrient;
		}

		$warnings[] = $warning;
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
