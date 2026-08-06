<?php

require_once __DIR__ . '/../../services/BaseService.php';
require_once __DIR__ . '/../../services/ProductNutritionService.php';
require_once __DIR__ . '/../../services/RecipeNutritionService.php';

$productNutritionSource = file_get_contents(__DIR__ . '/../../services/ProductNutritionService.php');
$recipeNutritionSource = file_get_contents(__DIR__ . '/../../services/RecipeNutritionService.php');
$migrationSource = file_get_contents(__DIR__ . '/../../migrations/0263.sql');

check($productNutritionSource !== false, 'failed to read ProductNutritionService.php');
check($recipeNutritionSource !== false, 'failed to read RecipeNutritionService.php');
check($migrationSource !== false, 'failed to read 0263.sql');

$nutrientKeys = [
	'calories',
	'protein',
	'carbs',
	'fat',
	'saturated_fat',
	'polyunsaturated_fat',
	'monounsaturated_fat',
	'trans_fat',
	'cholesterol',
	'sodium',
	'potassium',
	'dietary_fiber',
	'sugars',
	'vitamin_a',
	'vitamin_c',
	'calcium',
	'iron'
];

foreach ($nutrientKeys as $key)
{
	check(strpos($productNutritionSource, "'" . $key . "'") !== false, 'ProductNutritionService missing nutrient key ' . $key);
	check(strpos($recipeNutritionSource, "'" . $key . "'") !== false, 'RecipeNutritionService missing nutrient key ' . $key);
}

foreach (array_slice($nutrientKeys, 4) as $key)
{
	check(strpos($migrationSource, $key) !== false, '0263 migration missing nutrient column ' . $key);
}

check(strpos($productNutritionSource, "'carbohydrates'") === false, 'ProductNutritionService should not use carbohydrates');
check(strpos($recipeNutritionSource, "'carbohydrates'") === false, 'RecipeNutritionService should not use carbohydrates');
check(strpos($migrationSource, 'RENAME COLUMN carbohydrates TO carbs') !== false, '0263 migration should rename carbohydrates to carbs');

echo "product nutrition expanded fields contract ok\n";

function check($condition, $message)
{
	if (!$condition)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}
