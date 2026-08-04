<?php

$source = file_get_contents(__DIR__ . '/../../services/FoodLibraryService.php');
if ($source === false)
{
	fwrite(STDERR, "failed to read FoodLibraryService.php\n");
	exit(1);
}

function check_contains($source, $needle, $message)
{
	if (strpos($source, $needle) === false)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

check_contains($source, 'SearchLocalFoods', 'missing SearchLocalFoods');
check_contains($source, 'SearchFoodsWithFallback', 'missing SearchFoodsWithFallback');
check_contains($source, 'BooheeFoodSearchService::GetInstance()->SearchFoods', 'missing Boohee fallback search call');
check_contains($source, "'fallback' =>", 'missing fallback metadata');
check_contains($source, "'used' => true", 'missing fallback used=true');
check_contains($source, "'used' => false", 'missing fallback used=false');
check_contains($source, 'GROCY_BOOHEE_API_KEY', 'missing Boohee API key gate');
check_contains($source, "trim((string)GROCY_BOOHEE_API_KEY) !== ''", 'missing non-empty Boohee API key check');
check_contains($source, '(int)($localResult[\'pagination\'][\'totalCount\'] ?? 0)', 'missing local totalCount fallback gate');
check_contains($source, 'is_array($localResult[\'foods\'] ?? null) ? $localResult[\'foods\'] : []', 'missing foods array normalization');

require_once __DIR__ . '/../../services/BaseService.php';
require_once __DIR__ . '/../../services/FoodLibraryService.php';

if (!defined('GROCY_BOOHEE_API_KEY'))
{
	define('GROCY_BOOHEE_API_KEY', 'contract-test-key');
}

class FoodLibraryFallbackContractService extends \Grocy\Services\FoodLibraryService
{
	public function SearchLocalFoods($query = null, $page = 1, $pageSize = 20)
	{
		return [
			'pagination' => [
				'page' => $page,
				'pageSize' => $pageSize,
				'totalCount' => 3,
				'hasMore' => false
			]
		];
	}
}

$reflection = new ReflectionClass(FoodLibraryFallbackContractService::class);
$service = $reflection->newInstanceWithoutConstructor();

try
{
	$result = $service->SearchFoodsWithFallback('rice', 2, 20);
}
catch (Throwable $ex)
{
	fwrite(STDERR, 'local pagination fallback behavior threw: ' . $ex->getMessage() . "\n");
	exit(1);
}

if (!array_key_exists('foods', $result) || $result['foods'] !== [])
{
	fwrite(STDERR, "local result foods should be normalized to an empty array\n");
	exit(1);
}

if (($result['fallback']['used'] ?? null) !== false)
{
	fwrite(STDERR, "local totalCount > 0 should not use fallback when current page is empty\n");
	exit(1);
}

echo "food library fallback contract ok\n";
