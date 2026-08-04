<?php

require_once __DIR__ . '/../../services/BooheeFoodSearchService.php';

use Grocy\Services\BooheeFoodSearchService;

$source = file_get_contents(__DIR__ . '/../../services/BooheeFoodSearchService.php');
check(strpos($source, "'X-Api-Key' => " . '$apiKey') !== false, 'Boohee request should use X-Api-Key header');
check(strpos($source, "'Authorization' => 'Bearer ' . " . '$apiKey') === false, 'Boohee request should not use Bearer header');

$raw = [
	'name' => '鸡胸肉',
	'code' => 'boohee-chicken-breast',
	'calory' => '133',
	'protein' => '24.6',
	'fat' => '3.1',
	'carbohydrate' => '0',
];

$candidate = BooheeFoodSearchService::MapRawFoodForTest($raw);

check($candidate['id'] === null, 'id should be null');
check($candidate['name'] === '鸡胸肉', 'name should map from name');
check($candidate['imported'] === false, 'imported should be false');
check($candidate['source']['provider'] === 'boohee', 'provider should be boohee');
check($candidate['source']['external_id'] === 'boohee-chicken-breast', 'external id should map from code');
check($candidate['nutrition']['basis_amount'] === 100.0, 'basis amount should be 100');
check($candidate['nutrition']['basis_unit']['name'] === 'g', 'basis unit should be g');
check($candidate['nutrition']['calories'] === 133.0, 'calories should map from calory');
check($candidate['nutrition']['protein'] === 24.6, 'protein should map');
check($candidate['nutrition']['fat'] === 3.1, 'fat should map');
check($candidate['nutrition']['carbohydrates'] === 0.0, 'carbohydrates should map from carbohydrate');

$alternate = BooheeFoodSearchService::MapRawFoodForTest([
	'food_name' => '燕麦',
	'uuid' => 'boohee-oats',
	'energy' => 367,
	'protein' => '12.3',
	'fat' => '6.7',
	'carbs' => '61.6',
]);

check($alternate['name'] === '燕麦', 'name should map from food_name');
check($alternate['source']['external_id'] === 'boohee-oats', 'external id should map from uuid');
check($alternate['nutrition']['calories'] === 367.0, 'calories should map from energy');
check($alternate['nutrition']['carbohydrates'] === 61.6, 'carbohydrates should map from carbs');

$titleAndId = BooheeFoodSearchService::MapRawFoodForTest([
	'title' => '酸奶',
	'id' => 12345,
	'calories' => '72',
	'protein' => '3.5',
	'fat' => '2.9',
	'carbohydrate' => '8.4',
]);

check($titleAndId['name'] === '酸奶', 'name should map from title');
check($titleAndId['source']['external_id'] === '12345', 'external id should map from id');
check($titleAndId['nutrition']['calories'] === 72.0, 'calories should map from calories');

$detailShape = BooheeFoodSearchService::MapRawFoodForTest([
	'name' => '鱼香肉丝',
	'code' => 'yuxiangrousi2',
	'calories' => ['value' => 109, 'unit' => 'kcal'],
	'protein' => ['value' => 11.42, 'unit' => 'g'],
	'fat' => ['value' => 5.24, 'unit' => 'g'],
	'carbohydrate' => ['value' => 4.66, 'unit' => 'g'],
]);

check($detailShape['nutrition']['calories'] === 109.0, 'detail calories.value should map');
check($detailShape['nutrition']['protein'] === 11.42, 'detail protein.value should map');
check($detailShape['nutrition']['fat'] === 5.24, 'detail fat.value should map');
check($detailShape['nutrition']['carbohydrates'] === 4.66, 'detail carbohydrate.value should map');

$missingCarbs = $raw;
unset($missingCarbs['carbohydrate']);
check(BooheeFoodSearchService::MapRawFoodForTest($missingCarbs) === null, 'missing required carbohydrates should return null');

$searchResult = BooheeFoodSearchService::MapSearchResponseForTest([
	'data' => [
		'foods' => [$raw],
		'total_count' => 3,
		'has_more' => true
	]
], 1, 20);

check(count($searchResult['foods']) === 1, 'search response should map foods from data.foods');
check($searchResult['pagination']['totalCount'] === 3, 'search response should map nested total count');
check($searchResult['pagination']['hasMore'] === true, 'search response should map nested hasMore');

expectRuntimeException(function ()
{
	BooheeFoodSearchService::MapSearchResponseForTest(['data' => ['unexpected' => []]], 1, 20);
}, 'missing food list should throw');

expectRuntimeException(function ()
{
	BooheeFoodSearchService::MapSearchResponseForTest(['status' => 'error', 'message' => 'bad request'], 1, 20);
}, 'error envelope should throw');

echo "boohee mapper ok\n";

function check($condition, $message)
{
	if (!$condition)
	{
		throw new RuntimeException($message);
	}
}

function expectRuntimeException(callable $callback, $message)
{
	try
	{
		$callback();
	}
	catch (RuntimeException)
	{
		return;
	}

	throw new RuntimeException($message);
}
