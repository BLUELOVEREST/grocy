<?php

require_once __DIR__ . '/../../services/BooheeFoodSearchService.php';

use Grocy\Services\BooheeFoodSearchService;

$raw = [
	'name' => '鸡胸肉',
	'code' => 'boohee-chicken-breast',
	'calory' => '133',
	'protein' => '24.6',
	'fat' => '3.1',
	'carbohydrate' => '0',
];

$candidate = BooheeFoodSearchService::MapRawFoodForTest($raw);

assert($candidate['id'] === null);
assert($candidate['name'] === '鸡胸肉');
assert($candidate['imported'] === false);
assert($candidate['source']['provider'] === 'boohee');
assert($candidate['source']['external_id'] === 'boohee-chicken-breast');
assert($candidate['nutrition']['basis_amount'] === 100.0);
assert($candidate['nutrition']['basis_unit']['name'] === 'g');
assert($candidate['nutrition']['calories'] === 133.0);
assert($candidate['nutrition']['protein'] === 24.6);
assert($candidate['nutrition']['fat'] === 3.1);
assert($candidate['nutrition']['carbohydrates'] === 0.0);

echo "boohee mapper ok\n";
