<?php

require_once __DIR__ . '/../../services/BaseService.php';
require_once __DIR__ . '/../../services/ChinaFoodCompositionImportService.php';

use Grocy\Services\ChinaFoodCompositionImportService;

$source = file_get_contents(__DIR__ . '/../../services/ChinaFoodCompositionImportService.php');
check(strpos($source, "(string)\$item['foodName'] . '（'") === false, 'China Food import should not append foodCode to names');
check(strpos($source, 'BuildImportName') !== false, 'China Food import should uniquify duplicate names without foodCode');

$service = (new ReflectionClass(ChinaFoodCompositionImportService::class))->newInstanceWithoutConstructor();

$tempDir = sys_get_temp_dir() . '/china-food-import-test-' . getmypid();
mkdir($tempDir);
file_put_contents($tempDir . '/merged_test-a.json', json_encode([
	['foodCode' => '001', 'foodName' => '奶油'],
	['foodCode' => '002', 'foodName' => '黄油']
], JSON_UNESCAPED_UNICODE));
file_put_contents($tempDir . '/merged_test-b.json', json_encode([
	['foodCode' => '003', 'foodName' => '奶油']
], JSON_UNESCAPED_UNICODE));

$countFoodNames = reflect_private_method($service, 'CountFoodNames');
$nameCounts = $countFoodNames->invoke($service, glob($tempDir . '/*.json'));
check($nameCounts['奶油'] === 2, 'duplicate food names should be counted');
check($nameCounts['黄油'] === 1, 'unique food names should be counted');

$buildImportName = reflect_private_method($service, 'BuildImportName');
$usedNames = [];
check($buildImportName->invokeArgs($service, ['黄油', 'test-a', $nameCounts, &$usedNames]) === '黄油', 'unique food should keep base name');
check($buildImportName->invokeArgs($service, ['奶油', 'test-a', $nameCounts, &$usedNames]) === '奶油 [test-a]', 'duplicate food should include category');
check($buildImportName->invokeArgs($service, ['奶油', 'test-a', $nameCounts, &$usedNames]) === '奶油 [test-a] #2', 'same duplicate name/category should get numeric suffix');

$nullableNumber = reflect_private_method($service, 'NullableNumber');
check($nullableNumber->invoke($service, ['protein' => 'Tr', 'foodCode' => '004', 'foodName' => 'trace'], 'protein', 'file.json', 0) === null, 'trace nutrients should import as null');
check($nullableNumber->invoke($service, ['protein' => '—', 'foodCode' => '005', 'foodName' => 'dash'], 'protein', 'file.json', 0) === null, 'dash nutrients should import as null');
check($nullableNumber->invoke($service, ['protein' => '12.3', 'foodCode' => '006', 'foodName' => 'numeric'], 'protein', 'file.json', 0) === 12.3, 'numeric nutrients should import as float');

unlink($tempDir . '/merged_test-a.json');
unlink($tempDir . '/merged_test-b.json');
rmdir($tempDir);

echo "china food import service ok\n";

function reflect_private_method($object, $name)
{
	$method = new ReflectionMethod($object, $name);
	$method->setAccessible(true);
	return $method;
}

function check($condition, $message)
{
	if (!$condition)
	{
		throw new RuntimeException($message);
	}
}
