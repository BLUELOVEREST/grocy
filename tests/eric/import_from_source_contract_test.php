<?php

function check_contains($source, $needle, $message)
{
	if (strpos($source, $needle) === false)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

$controller = file_get_contents(__DIR__ . '/../../controllers/Api/FoodLibraryApiController.php');
if ($controller === false)
{
	fwrite(STDERR, "failed to read FoodLibraryApiController.php\n");
	exit(1);
}

$routes = file_get_contents(__DIR__ . '/../../routes.php');
if ($routes === false)
{
	fwrite(STDERR, "failed to read routes.php\n");
	exit(1);
}

$service = file_get_contents(__DIR__ . '/../../services/FoodLibraryService.php');
if ($service === false)
{
	fwrite(STDERR, "failed to read FoodLibraryService.php\n");
	exit(1);
}

check_contains($controller, 'function ImportFromSource', 'missing ImportFromSource controller action');
check_contains($controller, 'FoodLibraryService::GetInstance()->ImportFromSource($payload)', 'missing ImportFromSource service call');
check_contains($routes, "\$group->post('/eric/foods/import-from-source'", 'missing import-from-source POST route');
check_contains($service, 'public function ImportFromSource(array $payload)', 'missing ImportFromSource service method');
check_contains($service, "case 'boohee'", 'missing boohee provider import case');
check_contains($service, 'is_string($payload[$key])', 'missing string payload validation');
check_contains($service, 'is_int($payload[$key])', 'missing int payload validation');
check_contains($service, 'is_float($payload[$key])', 'missing float payload validation');

echo "import-from-source contract ok\n";
