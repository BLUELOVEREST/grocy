<?php

namespace Grocy\Controllers\Api;

use Grocy\Controllers\Users\User;
use Grocy\Services\RecipeNutritionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecipeNutritionApiController extends BaseApiController
{
	public function Get(Request $request, Response $response, array $args)
	{
		User::CheckPermission($request, User::PERMISSION_RECIPES);

		try
		{
			return $this->ApiResponse($response, RecipeNutritionService::GetInstance()->GetRecipeNutrition($args['recipeId']));
		}
		catch (\InvalidArgumentException $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage(), 400);
		}
	}
}
