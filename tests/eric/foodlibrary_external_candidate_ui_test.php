<?php

function check_contains($source, $needle, $message)
{
	if (strpos($source, $needle) === false)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

$viewJs = file_get_contents(__DIR__ . '/../../public/viewjs/foodlibrary.js');
if ($viewJs === false)
{
	fwrite(STDERR, "failed to read public/viewjs/foodlibrary.js\n");
	exit(1);
}

$view = file_get_contents(__DIR__ . '/../../views/foodlibrary.blade.php');
if ($view === false)
{
	fwrite(STDERR, "failed to read views/foodlibrary.blade.php\n");
	exit(1);
}

check_contains($viewJs, 'FoodLibraryIsExternalCandidate', 'missing FoodLibraryIsExternalCandidate helper');
check_contains($viewJs, 'FoodLibraryImportExternalCandidate', 'missing FoodLibraryImportExternalCandidate helper');
check_contains($viewJs, 'eric/foods/import-from-source', 'missing import-from-source API call');
check_contains($viewJs, 'data-provider', 'missing provider data attribute');
check_contains($viewJs, 'data-external-id', 'missing external id data attribute');
check_contains($viewJs, 'food-library-import-external', 'missing external import button class');
check_contains($viewJs, 'FoodLibraryIsExternalCandidate(row)', 'missing guard preventing product links for external candidates');
check_contains($view, "{{ \$__t('Actions') }}", 'missing Actions table header');

echo "foodlibrary external UI contract ok\n";
