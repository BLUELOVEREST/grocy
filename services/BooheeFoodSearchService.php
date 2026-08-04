<?php

namespace Grocy\Services;

require_once __DIR__ . '/BaseService.php';

class BooheeFoodSearchService extends BaseService
{
	const BASE_URL = 'https://api.boohee.com';

	public function SearchFoods($query, $page = 1, $pageSize = 20)
	{
		$queryText = trim((string)$query);
		$page = max(1, (int)$page);
		$pageSize = min(100, max(1, (int)$pageSize));

		if ($queryText === '')
		{
			return [
				'foods' => [],
				'pagination' => [
					'page' => $page,
					'pageSize' => $pageSize,
					'totalCount' => 0,
					'hasMore' => false
				]
			];
		}

		$response = $this->GetJson('/open-apis/v1/food/search', [
			'keyword' => $queryText,
			'page' => $page,
			'per_page' => $pageSize
		]);
		$rawFoods = $this->ExtractFoodList($response);
		$foods = [];
		foreach ($rawFoods as $rawFood)
		{
			if (!is_array($rawFood))
			{
				continue;
			}

			$food = self::MapRawFood($rawFood);
			if ($food !== null)
			{
				$foods[] = $food;
			}
		}

		$totalCount = $this->ExtractTotalCount($response, count($foods));

		return [
			'foods' => $foods,
			'pagination' => [
				'page' => $page,
				'pageSize' => $pageSize,
				'totalCount' => $totalCount,
				'hasMore' => $this->ExtractHasMore($response, $page, $pageSize, $totalCount)
			]
		];
	}

	public function ImportFoodByExternalId($externalId)
	{
		$externalId = trim((string)$externalId);
		if ($externalId === '')
		{
			throw new \InvalidArgumentException('External id is required');
		}

		$response = $this->GetJson('/open-apis/v1/food/detail', [
			'code' => $externalId
		]);
		$rawFood = $this->ExtractFoodDetail($response);
		$food = self::MapRawFood($rawFood);
		if ($food === null)
		{
			throw new \RuntimeException('Boohee food detail is missing required fields');
		}

		return FoodLibraryService::GetInstance()->ImportFood([
			'provider' => 'boohee',
			'external_id' => $food['source']['external_id'],
			'name' => $food['name'],
			'stock_unit' => 'g',
			'basis_amount' => 100,
			'basis_unit' => 'g',
			'calories' => $food['nutrition']['calories'],
			'protein' => $food['nutrition']['protein'],
			'fat' => $food['nutrition']['fat'],
			'carbohydrates' => $food['nutrition']['carbohydrates'],
			'aliases' => [],
			'category' => 'boohee',
			'source_payload' => $rawFood
		]);
	}

	public static function MapRawFoodForTest(array $raw)
	{
		return self::MapRawFood($raw);
	}

	private static function MapRawFood(array $raw)
	{
		$name = self::FirstTextValue($raw, ['name', 'food_name', 'title']);
		$externalId = self::FirstTextValue($raw, ['code', 'id', 'uuid']);
		$calories = self::FirstNumericValue($raw, ['calories', 'calory', 'energy']);
		$protein = self::FirstNumericValue($raw, ['protein']);
		$fat = self::FirstNumericValue($raw, ['fat']);
		$carbohydrates = self::FirstNumericValue($raw, ['carbohydrate', 'carbs']);

		if ($name === null || $externalId === null || $calories === null || $protein === null || $fat === null || $carbohydrates === null)
		{
			return null;
		}

		return [
			'id' => null,
			'name' => $name,
			'imported' => false,
			'stock_unit' => [
				'id' => null,
				'name' => 'g',
				'name_plural' => 'g'
			],
			'nutrition' => [
				'basis_amount' => 100.0,
				'basis_qu_id' => null,
				'basis_unit' => [
					'id' => null,
					'name' => 'g',
					'name_plural' => 'g'
				],
				'calories' => $calories,
				'protein' => $protein,
				'fat' => $fat,
				'carbohydrates' => $carbohydrates
			],
			'source' => [
				'type' => 'external',
				'provider' => 'boohee',
				'external_id' => $externalId,
				'category' => 'boohee',
				'source_payload' => $raw
			]
		];
	}

	private static function FirstTextValue(array $raw, array $keys)
	{
		foreach ($keys as $key)
		{
			if (array_key_exists($key, $raw) && is_scalar($raw[$key]))
			{
				$value = trim((string)$raw[$key]);
				if ($value !== '')
				{
					return $value;
				}
			}
		}

		return null;
	}

	private static function FirstNumericValue(array $raw, array $keys)
	{
		foreach ($keys as $key)
		{
			if (array_key_exists($key, $raw) && $raw[$key] !== null && !(is_string($raw[$key]) && trim($raw[$key]) === '') && is_numeric($raw[$key]))
			{
				return (float)$raw[$key];
			}
		}

		return null;
	}

	private function ExtractFoodList(array $response)
	{
		foreach (['foods', 'items', 'list'] as $key)
		{
			if (array_key_exists($key, $response) && is_array($response[$key]))
			{
				return $response[$key];
			}
		}

		if (array_key_exists('data', $response) && is_array($response['data']))
		{
			foreach (['foods', 'items', 'list'] as $key)
			{
				if (array_key_exists($key, $response['data']) && is_array($response['data'][$key]))
				{
					return $response['data'][$key];
				}
			}

			if ($this->IsListArray($response['data']))
			{
				return $response['data'];
			}
		}

		return [];
	}

	private function ExtractFoodDetail(array $response)
	{
		foreach (['food', 'item', 'data'] as $key)
		{
			if (array_key_exists($key, $response) && is_array($response[$key]))
			{
				return $response[$key];
			}
		}

		return $response;
	}

	private function ExtractTotalCount(array $response, $fallback)
	{
		foreach (['total_count', 'totalCount', 'total'] as $key)
		{
			if (array_key_exists($key, $response) && is_numeric($response[$key]))
			{
				return (int)$response[$key];
			}
		}

		if (array_key_exists('pagination', $response) && is_array($response['pagination']))
		{
			return $this->ExtractTotalCount($response['pagination'], $fallback);
		}

		if (array_key_exists('meta', $response) && is_array($response['meta']))
		{
			return $this->ExtractTotalCount($response['meta'], $fallback);
		}

		if (array_key_exists('data', $response) && is_array($response['data']))
		{
			return $this->ExtractTotalCount($response['data'], $fallback);
		}

		return (int)$fallback;
	}

	private function ExtractHasMore(array $response, $page, $pageSize, $totalCount)
	{
		foreach (['has_more', 'hasMore'] as $key)
		{
			if (array_key_exists($key, $response))
			{
				return filter_var($response[$key], FILTER_VALIDATE_BOOLEAN);
			}
		}

		if (array_key_exists('pagination', $response) && is_array($response['pagination']))
		{
			return $this->ExtractHasMore($response['pagination'], $page, $pageSize, $totalCount);
		}

		if (array_key_exists('meta', $response) && is_array($response['meta']))
		{
			return $this->ExtractHasMore($response['meta'], $page, $pageSize, $totalCount);
		}

		return $page * $pageSize < $totalCount;
	}

	private function IsListArray(array $array)
	{
		return array_keys($array) === range(0, count($array) - 1);
	}

	private function GetJson($path, array $queryParams)
	{
		$apiKey = $this->GetApiKey();
		$url = self::BASE_URL . $path . '?' . http_build_query($queryParams);
		$context = stream_context_create([
			'http' => [
				'method' => 'GET',
				'header' => [
					'Accept: application/json',
					'Authorization: Bearer ' . $apiKey
				],
				'ignore_errors' => true
			]
		]);

		$response = file_get_contents($url, false, $context);
		if ($response === false)
		{
			throw new \RuntimeException('Boohee API request failed');
		}

		if (isset($http_response_header[0]) && preg_match('/\s([0-9]{3})\s/', $http_response_header[0], $matches) === 1 && (int)$matches[1] >= 400)
		{
			throw new \RuntimeException('Boohee API request failed: HTTP ' . $matches[1]);
		}

		$decoded = json_decode($response, true);
		if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded))
		{
			throw new \RuntimeException('Boohee API returned invalid JSON');
		}

		return $decoded;
	}

	private function GetApiKey()
	{
		$apiKey = defined('GROCY_BOOHEE_API_KEY') ? GROCY_BOOHEE_API_KEY : getenv('GROCY_BOOHEE_API_KEY');
		if (!is_string($apiKey) || trim($apiKey) === '')
		{
			throw new \RuntimeException('GROCY_BOOHEE_API_KEY is required');
		}

		return trim($apiKey);
	}
}
