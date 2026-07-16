<?php

namespace Grocy\Services;

class ProductPropertyTemplatesService extends BaseService
{
	const PROPERTY_TYPE_CHECKBOX = 'checkbox';
	const PROPERTY_TYPE_NUMBER = 'number';
	const PROPERTY_TYPE_SELECT = 'select';
	const PROPERTY_TYPE_TEXT = 'text';

	public function GetTemplate(int $parentProductId)
	{
		$this->EnsureProductExists($parentProductId);

		return $this->RowsToArray($this->DB->product_property_definitions()
			->where('parent_product_id', $parentProductId)
			->orderBy('sort_number')
			->orderBy('label', 'COLLATE NOCASE'));
	}

	public function ReplaceTemplate(int $parentProductId, array $definitions)
	{
		$this->EnsureProductExists($parentProductId);

		$keptDefinitionIds = [];
		$sortNumber = 10;

		foreach ($definitions as $definition)
		{
			$normalizedDefinition = $this->NormalizeDefinition($definition, $sortNumber);
			$sortNumber += 10;

			if (isset($definition['id']) && filter_var($definition['id'], FILTER_VALIDATE_INT) !== false)
			{
				$definitionRow = $this->DB->product_property_definitions()->where('id = :1 AND parent_product_id = :2', $definition['id'], $parentProductId)->fetch();
				if ($definitionRow === null)
				{
					throw new \Exception('Property definition does not exist');
				}

				$definitionRow->update($normalizedDefinition);
				$keptDefinitionIds[] = intval($definition['id']);
			}
			else
			{
				$newDefinition = $this->DB->product_property_definitions()->createRow(array_merge($normalizedDefinition, [
					'parent_product_id' => $parentProductId
				]));
				$newDefinition->save();
				$keptDefinitionIds[] = intval($newDefinition->id);
			}
		}

		if (count($keptDefinitionIds) === 0)
		{
			DatabaseService::GetInstance()->ExecuteDbStatement('DELETE FROM product_property_definitions WHERE parent_product_id = ?', [$parentProductId]);
		}
		else
		{
			$placeholders = implode(',', array_fill(0, count($keptDefinitionIds), '?'));
			DatabaseService::GetInstance()->ExecuteDbStatement(
				"DELETE FROM product_property_definitions WHERE parent_product_id = ? AND id NOT IN ($placeholders)",
				array_merge([$parentProductId], $keptDefinitionIds)
			);
		}

		return $this->GetTemplate($parentProductId);
	}

	public function GetProperties(int $productId)
	{
		$product = $this->EnsureProductExists($productId);
		$parentProductId = $this->GetTemplateParentProductId($product);
		$definitions = $this->GetTemplate($parentProductId);
		$values = $this->DB->product_property_values()->where('product_id', $productId);
		$valuesByDefinitionId = [];

		foreach ($values as $value)
		{
			$valuesByDefinitionId[intval($value->property_definition_id)] = $value->value;
		}

		foreach ($definitions as &$definition)
		{
			$definition['value'] = $valuesByDefinitionId[intval($definition['id'])] ?? null;
		}

		return [
			'product_id' => $productId,
			'parent_product_id' => $parentProductId,
			'definitions' => $definitions
		];
	}

	public function ReplaceProperties(int $productId, array $values)
	{
		$product = $this->EnsureProductExists($productId);
		$parentProductId = $this->GetTemplateParentProductId($product);
		$validDefinitionIds = [];

		foreach ($this->GetTemplate($parentProductId) as $definition)
		{
			$validDefinitionIds[] = intval($definition['id']);
		}

		$keptValueDefinitionIds = [];

		foreach ($values as $value)
		{
			if (!isset($value['property_definition_id']) || filter_var($value['property_definition_id'], FILTER_VALIDATE_INT) === false)
			{
				throw new \Exception('Property definition id is required');
			}

			$definitionId = intval($value['property_definition_id']);
			if (!in_array($definitionId, $validDefinitionIds))
			{
				throw new \Exception('Property definition is not valid for this product');
			}

			$propertyValue = isset($value['value']) ? strval($value['value']) : null;
			$existingValue = $this->DB->product_property_values()->where('product_id = :1 AND property_definition_id = :2', $productId, $definitionId)->fetch();
			if ($existingValue === null)
			{
				$newValue = $this->DB->product_property_values()->createRow([
					'product_id' => $productId,
					'property_definition_id' => $definitionId,
					'value' => $propertyValue
				]);
				$newValue->save();
			}
			else
			{
				$existingValue->update([
					'value' => $propertyValue
				]);
			}

			$keptValueDefinitionIds[] = $definitionId;
		}

		if (count($validDefinitionIds) > 0)
		{
			$placeholders = implode(',', array_fill(0, count($validDefinitionIds), '?'));
			if (count($keptValueDefinitionIds) === 0)
			{
				DatabaseService::GetInstance()->ExecuteDbStatement(
					"DELETE FROM product_property_values WHERE product_id = ? AND property_definition_id IN ($placeholders)",
					array_merge([$productId], $validDefinitionIds)
				);
			}
			else
			{
				$keptPlaceholders = implode(',', array_fill(0, count($keptValueDefinitionIds), '?'));
				DatabaseService::GetInstance()->ExecuteDbStatement(
					"DELETE FROM product_property_values WHERE product_id = ? AND property_definition_id IN ($placeholders) AND property_definition_id NOT IN ($keptPlaceholders)",
					array_merge([$productId], $validDefinitionIds, $keptValueDefinitionIds)
				);
			}
		}

		return $this->GetProperties($productId);
	}

	private function EnsureProductExists(int $productId)
	{
		$product = $this->DB->products($productId);
		if ($product === null)
		{
			throw new \Exception('Product does not exist');
		}

		return $product;
	}

	private function GetTemplateParentProductId($product)
	{
		if (!empty($product->parent_product_id))
		{
			return intval($product->parent_product_id);
		}

		return intval($product->id);
	}

	private function NormalizeDefinition(array $definition, int $defaultSortNumber)
	{
		$label = trim(strval($definition['label'] ?? ''));
		$name = trim(strval($definition['name'] ?? ''));
		$type = trim(strval($definition['type'] ?? self::PROPERTY_TYPE_TEXT));

		if ($label === '')
		{
			throw new \Exception('Property label is required');
		}

		if ($name === '')
		{
			$name = $this->NormalizeName($label);
		}

		if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name))
		{
			throw new \Exception('Property name must start with a letter or underscore and contain only letters, numbers, and underscores');
		}

		if (!in_array($type, $this->GetAllowedTypes()))
		{
			throw new \Exception('Invalid property type');
		}

		return [
			'name' => $name,
			'label' => $label,
			'type' => $type,
			'unit' => $this->NormalizeNullableString($definition['unit'] ?? null),
			'options' => $this->NormalizeNullableString($definition['options'] ?? null),
			'input_required' => !empty($definition['input_required']) ? 1 : 0,
			'sort_number' => isset($definition['sort_number']) && filter_var($definition['sort_number'], FILTER_VALIDATE_INT) !== false ? intval($definition['sort_number']) : $defaultSortNumber
		];
	}

	private function NormalizeName(string $label)
	{
		$name = strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $label), '_'));
		if ($name === '' || preg_match('/^[0-9]/', $name))
		{
			$name = 'property_' . $name;
		}

		return $name;
	}

	private function NormalizeNullableString($value)
	{
		if ($value === null)
		{
			return null;
		}

		$value = trim(strval($value));
		return $value === '' ? null : $value;
	}

	private function GetAllowedTypes()
	{
		return [
			self::PROPERTY_TYPE_CHECKBOX,
			self::PROPERTY_TYPE_NUMBER,
			self::PROPERTY_TYPE_SELECT,
			self::PROPERTY_TYPE_TEXT
		];
	}

	private function RowsToArray($rows)
	{
		$return = [];
		foreach ($rows as $row)
		{
			$return[] = [
				'id' => intval($row->id),
				'parent_product_id' => intval($row->parent_product_id),
				'name' => $row->name,
				'label' => $row->label,
				'type' => $row->type,
				'unit' => $row->unit,
				'options' => $row->options,
				'input_required' => intval($row->input_required),
				'sort_number' => $row->sort_number === null ? null : intval($row->sort_number)
			];
		}

		return $return;
	}
}
