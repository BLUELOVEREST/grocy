<?php

namespace Grocy\Controllers\Api;

use Grocy\Controllers\Users\User;
use Grocy\Services\PrintService;
use Grocy\Services\StockService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PrintApiController extends BaseApiController
{
	public function PrintShoppingListThermal(Request $request, Response $response, array $args)
	{
		try
		{
			User::CheckPermission($request, User::PERMISSION_SHOPPINGLIST);

			$params = $request->getQueryParams();

			if (!isset($params['list']) || filter_var($params['list'], FILTER_VALIDATE_INT) === false || intval($params['list']) <= 0)
			{
				throw new \Exception('A shopping list id is required');
			}
			$listId = intval($params['list']);

			$printHeader = true;
			if (isset($params['printHeader']))
			{
				$printHeader = ($params['printHeader'] === 'true');
			}
			$items = StockService::GetInstance()->GetShoppinglistInPrintableStrings($listId);
			return $this->ApiResponse($response, PrintService::GetInstance()->printShoppingList($printHeader, $items));
		}
		catch (\Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}
}
