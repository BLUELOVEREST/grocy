<?php

use Grocy\Services\ChinaFoodCompositionImportService;

if (PHP_SAPI !== 'cli')
{
	exit('This script can only be run from the command line.' . PHP_EOL);
}

if ($argc < 2)
{
	fwrite(STDERR, 'Usage: php scripts/import-china-food-composition.php <china-food-data-dir>' . PHP_EOL);
	exit(2);
}

$root = dirname(__DIR__);

if (file_exists($root . '/embedded.txt'))
{
	define('GROCY_IS_EMBEDDED_INSTALL', true);
	define('GROCY_DATAPATH', file_get_contents($root . '/embedded.txt'));
	define('GROCY_USER_ID', 1);
}
else
{
	define('GROCY_IS_EMBEDDED_INSTALL', false);

	$datapath = 'data';
	if (getenv('GROCY_DATAPATH') !== false)
	{
		$datapath = getenv('GROCY_DATAPATH');
	}
	elseif (array_key_exists('GROCY_DATAPATH', $_SERVER))
	{
		$datapath = $_SERVER['GROCY_DATAPATH'];
	}

	if ($datapath[0] != '/')
	{
		$datapath = $root . '/' . $datapath;
	}

	define('GROCY_DATAPATH', $datapath);
}

$autoloadFile = $root . '/packages/autoload.php';
if (!is_file($autoloadFile))
{
	fwrite(STDERR, 'Missing Composer autoload file. Run this script inside the built Grocy container or install PHP dependencies with composer install.' . PHP_EOL);
	exit(1);
}

$configFile = GROCY_DATAPATH . '/config.php';
if (!is_file($configFile))
{
	fwrite(STDERR, 'Missing Grocy config file: ' . $configFile . PHP_EOL);
	exit(1);
}

require_once $root . '/helpers/PrerequisiteChecker.php';

try
{
	(new Grocy\Helpers\PrerequisiteChecker())->checkRequirements();
}
catch (Grocy\Helpers\ERequirementNotMet $ex)
{
	fwrite(STDERR, 'Unable to run Grocy: ' . $ex->getMessage() . PHP_EOL);
	exit(1);
}

require_once $autoloadFile;
require_once $configFile;
require_once $root . '/config-dist.php';

if ((GROCY_MODE === 'dev' || GROCY_MODE === 'demo' || GROCY_MODE === 'prerelease') && !defined('GROCY_USER_ID'))
{
	define('GROCY_USER_ID', 1);
}

if (GROCY_DISABLE_AUTH === true && !defined('GROCY_USER_ID'))
{
	define('GROCY_USER_ID', 1);
}

$result = ChinaFoodCompositionImportService::GetInstance()->ImportDirectory($argv[1]);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
