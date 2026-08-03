<?php

namespace Grocy\Controllers\Api;

use Grocy\Controllers\Users\User;
use Grocy\Services\FoodLibraryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FoodLibraryApiController extends BaseApiController
{
	public function Search(Request $request, Response $response, array $args)
	{
		User::CheckPermission($request, User::PERMISSION_RECIPES);

		$query = $request->getQueryParams()['query'] ?? '';
		$page = $request->getQueryParams()['page'] ?? 1;
		$pageSize = $request->getQueryParams()['page_size'] ?? 20;
		return $this->ApiResponse($response, FoodLibraryService::GetInstance()->SearchFoods($query, $page, $pageSize));
	}

	public function Get(Request $request, Response $response, array $args)
	{
		User::CheckPermission($request, User::PERMISSION_RECIPES);

		try
		{
			return $this->ApiResponse($response, FoodLibraryService::GetInstance()->GetFood($args['productId']));
		}
		catch (\InvalidArgumentException $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function Import(Request $request, Response $response, array $args)
	{
		User::CheckPermission($request, User::PERMISSION_RECIPES);

		try
		{
			$payload = $request->getParsedBody();
			if (!is_array($payload))
			{
				$payload = json_decode($request->getBody()->getContents(), true);
			}
			if (!is_array($payload))
			{
				return $this->GenericErrorResponse($response, 'Invalid request body', 400);
			}

			return $this->ApiResponse($response, FoodLibraryService::GetInstance()->ImportFood($payload));
		}
		catch (\InvalidArgumentException $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
