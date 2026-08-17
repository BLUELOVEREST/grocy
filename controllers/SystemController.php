<?php

namespace Grocy\Controllers;

use Grocy\Services\ApplicationService;
use Grocy\Services\DatabaseMigrationService;
use Grocy\Services\DemoDataGeneratorService;
use Grocy\Services\StockService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SystemController extends BaseController
{
	public function About(Request $request, Response $response, array $args)
	{
		return $this->RenderPage($response, 'about', [
			'systemInfo' => ApplicationService::GetInstance()->GetSystemInfo(),
			'versionInfo' => ApplicationService::GetInstance()->GetInstalledVersion(),
			'changelog' => ApplicationService::GetInstance()->GetChangelog()
		]);
	}

	public function BarcodeScannerTesting(Request $request, Response $response, array $args)
	{
		return $this->RenderPage($response, 'barcodescannertesting');
	}

	public function EricDashboard(Request $request, Response $response, array $args)
	{
		$shoppingLists = $this->DB->shopping_lists_view()->orderBy('name', 'COLLATE NOCASE');
		$selectedShoppingList = $this->DB->shopping_lists_view()->orderBy('name', 'COLLATE NOCASE')->fetch();
		$shoppingListItems = [];

		if ($selectedShoppingList !== null)
		{
			$shoppingListItems = $this->DB->uihelper_shopping_list()
				->where('shopping_list_id = :1', $selectedShoppingList->id)
				->orderBy('product_name', 'COLLATE NOCASE')
				->limit(8);
		}

		$productsForSearch = [];
		foreach ($this->DB->products()->where('active = 1')->orderBy('name', 'COLLATE NOCASE') as $product)
		{
			$productsForSearch[] = [
				'id' => (int)$product->id,
				'name' => $product->name,
				'isFood' => isset($product->is_food) && (int)$product->is_food === 1
			];
		}

		return $this->RenderPage($response, 'ericdashboard', [
			'productsForSearch' => $productsForSearch,
			'shoppingLists' => $shoppingLists,
			'selectedShoppingList' => $selectedShoppingList,
			'shoppingListItems' => $shoppingListItems,
			'missingProducts' => StockService::GetInstance()->GetMissingProducts(),
			'dueProducts' => StockService::GetInstance()->GetDueProducts(5, true),
			'expiredProducts' => StockService::GetInstance()->GetExpiredProducts(),
			'recentStockTransactions' => $this->DB->uihelper_stock_journal()->orderBy('row_created_timestamp', 'DESC')->limit(6)
		]);
	}

	public function Root(Request $request, Response $response, array $args)
	{
		// Schema migration is done here
		$databaseMigrationService = DatabaseMigrationService::GetInstance();
		$databaseMigrationService->MigrateDatabase();

		if (GROCY_MODE === 'dev' || GROCY_MODE === 'demo' || GROCY_MODE === 'prerelease')
		{
			$demoDataGeneratorService = DemoDataGeneratorService::GetInstance();
			$demoDataGeneratorService->PopulateDemoData(isset($request->getQueryParams()['nodemodata']));
		}

		return $response->withRedirect($this->AppContainer->get('UrlManager')->ConstructUrl($this->GetEntryPageRelative()));
	}

	public function Manifest(Request $request, Response $response, array $args)
	{
		$data = explode('#', base64_decode($request->getQueryParams()['data']));

		$manifest = [
			'name' => 'Grocy ' . $data[0],
			'short_name' => 'Grocy ' . $data[0],
			'icons' => [[
				'src' => './img/icon-1024.png',
				'sizes'=> '1024x1024',
				'type' => 'image/png'
			]],
			'start_url' => $data[1],
			'background_color' => '#333131',
			'theme_color' => '#333131',
			'display' => 'standalone'
		];

		$response->getBody()->write(json_encode($manifest));
		return $response->withHeader('Content-Type', 'application/json');
	}

	private function GetEntryPageRelative()
	{
		if (defined('GROCY_ENTRY_PAGE'))
		{
			$entryPage = constant('GROCY_ENTRY_PAGE');
		}
		else
		{
			$entryPage = 'stock';
		}

		// Stock
		if ($entryPage === 'stock' && constant('GROCY_FEATURE_FLAG_STOCK'))
		{
			return '/stockoverview';
		}

		// Shoppinglist
		if ($entryPage === 'shoppinglist' && constant('GROCY_FEATURE_FLAG_SHOPPINGLIST'))
		{
			return '/shoppinglist';
		}

		// Recipes
		if ($entryPage === 'recipes' && constant('GROCY_FEATURE_FLAG_RECIPES'))
		{
			return '/recipes';
		}

		// Chores
		if ($entryPage === 'chores' && constant('GROCY_FEATURE_FLAG_CHORES'))
		{
			return '/choresoverview';
		}

		// Tasks
		if ($entryPage === 'tasks' && constant('GROCY_FEATURE_FLAG_TASKS'))
		{
			return '/tasks';
		}

		// Batteries
		if ($entryPage === 'batteries' && constant('GROCY_FEATURE_FLAG_BATTERIES'))
		{
			return '/batteriesoverview';
		}

		if ($entryPage === 'equipment' && constant('GROCY_FEATURE_FLAG_EQUIPMENT'))
		{
			return '/equipment';
		}

		// Calendar
		if ($entryPage === 'calendar' && constant('GROCY_FEATURE_FLAG_CALENDAR'))
		{
			return '/calendar';
		}

		// Meal Plan
		if ($entryPage === 'mealplan' && constant('GROCY_FEATURE_FLAG_RECIPES_MEALPLAN'))
		{
			return '/mealplan';
		}

		return '/about';
	}
}
