<?php

function check_contains($source, $needle, $message)
{
	if (strpos($source, $needle) === false)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

function check_same($actual, $expected, $message)
{
	if ($actual !== $expected)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

function check_required_payload_rejects($payload, $key)
{
	try
	{
		\Grocy\Services\FoodLibraryService::RequiredPayloadValueForTest($payload, $key);
	}
	catch (\InvalidArgumentException $ex)
	{
		check_same($ex->getMessage(), $key . ' is required', 'wrong validation message for ' . $key);
		return;
	}

	fwrite(STDERR, 'expected InvalidArgumentException for ' . $key . "\n");
	exit(1);
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
check_contains($service, 'RequiredPayloadValueForTest', 'missing RequiredPayloadValue test wrapper');
check_contains($service, 'SELECT id', 'default location lookup should use direct SQL');
check_contains($service, 'FROM locations', 'default location lookup should query locations directly');
if (strpos($service, "->locations()->where('active = 1')->order('id')") !== false)
{
	fwrite(STDERR, "default location lookup should not use LessQL order('id')\n");
	exit(1);
}

require_once __DIR__ . '/../../services/BaseService.php';
require_once __DIR__ . '/../../services/FoodLibraryService.php';

set_error_handler(function ($severity, $message, $file, $line) {
	throw new \ErrorException($message, 0, $severity, $file, $line);
});

try
{
	check_required_payload_rejects(['provider' => []], 'provider');
	check_required_payload_rejects(['external_id' => (object)[]], 'external_id');
	check_same(\Grocy\Services\FoodLibraryService::RequiredPayloadValueForTest(['provider' => '  boohee  '], 'provider'), 'boohee', 'string payload should be trimmed');
	check_same(\Grocy\Services\FoodLibraryService::RequiredPayloadValueForTest(['external_id' => 123.45], 'external_id'), '123.45', 'numeric payload should be returned as a trimmed string');
}
catch (\ErrorException $ex)
{
	restore_error_handler();
	fwrite(STDERR, 'payload validation emitted warning/notice: ' . $ex->getMessage() . "\n");
	exit(1);
}

restore_error_handler();

echo "import-from-source contract ok\n";
