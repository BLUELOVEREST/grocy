<?php

namespace Grocy\Controllers\Api;

use Grocy\Services\ProductPropertyTemplatesService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductPropertiesApiController extends BaseApiController
{
	public function GetTemplate(Request $request, Response $response, array $args)
	{
		try
		{
			return $this->ApiResponse($response, ProductPropertyTemplatesService::GetInstance()->GetTemplate(intval($args['parentProductId'])));
		}
		catch (\Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function SetTemplate(Request $request, Response $response, array $args)
	{
		try
		{
			$requestBody = $this->GetParsedAndFilteredRequestBody($request);
			return $this->ApiResponse($response, ProductPropertyTemplatesService::GetInstance()->ReplaceTemplate(intval($args['parentProductId']), $requestBody['definitions'] ?? []));
		}
		catch (\Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function GetProperties(Request $request, Response $response, array $args)
	{
		try
		{
			return $this->ApiResponse($response, ProductPropertyTemplatesService::GetInstance()->GetProperties(intval($args['productId'])));
		}
		catch (\Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}

	public function SetProperties(Request $request, Response $response, array $args)
	{
		try
		{
			$requestBody = $this->GetParsedAndFilteredRequestBody($request);
			return $this->ApiResponse($response, ProductPropertyTemplatesService::GetInstance()->ReplaceProperties(intval($args['productId']), $requestBody['values'] ?? []));
		}
		catch (\Exception $ex)
		{
			return $this->GenericErrorResponse($response, $ex->getMessage());
		}
	}
}
