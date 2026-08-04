<?php

namespace Grocy\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

require_once __DIR__ . '/BaseService.php';

class BooheeFoodSearchService extends BaseService
{
	const BASE_URL = 'https://api.boohee.com';
	const CONNECT_TIMEOUT_SECONDS = 3.0;
	const TIMEOUT_SECONDS = 8.0;

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
		return self::MapSearchResponse($response, $page, $pageSize);
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

	public static function MapSearchResponseForTest(array $response, $page = 1, $pageSize = 20)
	{
		return self::MapSearchResponse($response, $page, $pageSize);
	}

	private static function MapSearchResponse(array $response, $page, $pageSize)
	{
		self::ThrowIfErrorEnvelope($response);

		$page = max(1, (int)$page);
		$pageSize = min(100, max(1, (int)$pageSize));
		$rawFoods = self::ExtractFoodList($response);
		if ($rawFoods === null)
		{
			throw new \RuntimeException('Boohee API response is missing food list');
		}

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

		$totalCount = self::ExtractTotalCount($response, count($foods));

		return [
			'foods' => $foods,
			'pagination' => [
				'page' => $page,
				'pageSize' => $pageSize,
				'totalCount' => $totalCount,
				'hasMore' => self::ExtractHasMore($response, $page, $pageSize, $totalCount)
			]
		];
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

	private static function ExtractFoodList(array $response)
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

		}

		return null;
	}

	private static function ExtractFoodDetail(array $response)
	{
		self::ThrowIfErrorEnvelope($response);

		foreach (['food', 'item', 'data'] as $key)
		{
			if (array_key_exists($key, $response) && is_array($response[$key]))
			{
				return $response[$key];
			}
		}

		return $response;
	}

	private static function ExtractTotalCount(array $response, $fallback)
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
			return self::ExtractTotalCount($response['pagination'], $fallback);
		}

		if (array_key_exists('meta', $response) && is_array($response['meta']))
		{
			return self::ExtractTotalCount($response['meta'], $fallback);
		}

		if (array_key_exists('data', $response) && is_array($response['data']))
		{
			return self::ExtractTotalCount($response['data'], $fallback);
		}

		return (int)$fallback;
	}

	private static function ExtractHasMore(array $response, $page, $pageSize, $totalCount)
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
			return self::ExtractHasMore($response['pagination'], $page, $pageSize, $totalCount);
		}

		if (array_key_exists('meta', $response) && is_array($response['meta']))
		{
			return self::ExtractHasMore($response['meta'], $page, $pageSize, $totalCount);
		}

		if (array_key_exists('data', $response) && is_array($response['data']))
		{
			return self::ExtractHasMore($response['data'], $page, $pageSize, $totalCount);
		}

		return $page * $pageSize < $totalCount;
	}

	private static function ThrowIfErrorEnvelope(array $response)
	{
		$errorText = self::FirstTextValue($response, ['error']);
		if ($errorText !== null)
		{
			throw new \RuntimeException('Boohee API error: ' . $errorText);
		}

		$message = self::FirstTextValue($response, ['message', 'msg']);
		if ($message !== null && self::HasFailureCodeOrStatus($response))
		{
			throw new \RuntimeException('Boohee API error: ' . $message);
		}
	}

	private static function HasFailureCodeOrStatus(array $response)
	{
		foreach (['code', 'status'] as $key)
		{
			if (!array_key_exists($key, $response))
			{
				continue;
			}

			$value = $response[$key];
			if (is_bool($value))
			{
				return $value === false;
			}

			if (is_numeric($value))
			{
				$numericValue = (int)$value;
				return $numericValue !== 0 && $numericValue !== 200;
			}

			if (is_string($value))
			{
				$normalized = strtolower(trim($value));
				return in_array($normalized, ['error', 'fail', 'failed', 'failure', 'false'], true);
			}
		}

		return false;
	}

	private function GetJson($path, array $queryParams)
	{
		$apiKey = $this->GetApiKey();
		$client = new Client([
			'base_uri' => self::BASE_URL,
			'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
			'timeout' => self::TIMEOUT_SECONDS,
			'http_errors' => false
		]);

		try
		{
			$response = $client->request('GET', $path, [
				'query' => $queryParams,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $apiKey
				]
			]);
		}
		catch (GuzzleException $ex)
		{
			throw new \RuntimeException('Boohee API request failed', 0, $ex);
		}

		$statusCode = $response->getStatusCode();
		if ($statusCode < 200 || $statusCode >= 300)
		{
			throw new \RuntimeException('Boohee API request failed: HTTP ' . $statusCode);
		}

		$response = (string)$response->getBody();
		$decoded = json_decode($response, true);
		if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded))
		{
			throw new \RuntimeException('Boohee API returned invalid JSON');
		}

		self::ThrowIfErrorEnvelope($decoded);

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
