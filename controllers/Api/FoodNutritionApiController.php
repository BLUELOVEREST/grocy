<?php

namespace Grocy\Controllers\Api;

use Grocy\Services\ProductNutritionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FoodNutritionApiController extends BaseApiController
{
	public function Get(Request $request, Response $response, array $args)
	{
		try
		{
			return $this->ApiResponse($response, ProductNutritionService::GetInstance()->GetNutrition($args['productId']));
		}
		catch (\InvalidArgumentException $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}

	public function Put(Request $request, Response $response, array $args)
	{
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

			return $this->ApiResponse($response, ProductNutritionService::GetInstance()->SaveNutrition($args['productId'], $payload));
		}
		catch (\InvalidArgumentException $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
