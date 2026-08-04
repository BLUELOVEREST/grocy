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

echo "food library fallback contract ok\n";
