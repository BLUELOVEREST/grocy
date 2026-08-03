<?php

namespace Grocy\Services;

class ChinaFoodCompositionImportService extends BaseService
{
	public function ImportDirectory($dataDir)
	{
		if (!is_dir($dataDir))
		{
			throw new \InvalidArgumentException('China Food data directory not found: ' . $dataDir);
		}

		$files = glob(rtrim($dataDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.json');
		if ($files === false)
		{
			$files = [];
		}
		sort($files, SORT_STRING);
		if (count($files) === 0)
		{
			throw new \InvalidArgumentException('No China Food JSON files found in: ' . $dataDir);
		}

		$imported = 0;
		foreach ($files as $file)
		{
			if (!is_readable($file))
			{
				throw new \InvalidArgumentException('China Food JSON file is not readable: ' . $file);
			}

			$items = json_decode(file_get_contents($file), true);
			if (json_last_error() !== JSON_ERROR_NONE)
			{
				throw new \InvalidArgumentException('Invalid JSON file: ' . $file . ' (' . json_last_error_msg() . ')');
			}

			if (!is_array($items))
			{
				throw new \InvalidArgumentException('Invalid JSON file: ' . $file . ' (expected array)');
			}

			$category = $this->CategoryFromFilename($file);
			foreach ($items as $rowIndex => $item)
			{
				$this->ValidateItem($item, $file, $rowIndex);

				FoodLibraryService::GetInstance()->ImportFood([
					'provider' => 'china-food-composition',
					'external_id' => (string)$item['foodCode'],
					'name' => (string)$item['foodName'],
					'description' => $category,
					'category' => $category,
					'stock_unit' => 'g',
					'basis_amount' => 100,
					'basis_unit' => 'g',
					'calories' => $this->NullableNumber($item, 'energyKCal', $file, $rowIndex),
					'protein' => $this->NullableNumber($item, 'protein', $file, $rowIndex),
					'fat' => $this->NullableNumber($item, 'fat', $file, $rowIndex),
					'carbohydrates' => $this->NullableNumber($item, 'CHO', $file, $rowIndex),
					'source_payload' => $item
				]);
				$imported++;
			}
		}

		return [
			'files' => count($files),
			'imported' => $imported
		];
	}

	private function ValidateItem($item, $file, $rowIndex)
	{
		if (!is_array($item))
		{
			throw new \InvalidArgumentException('Invalid China Food item in ' . $file . ' at row ' . $rowIndex . ': item is not an object');
		}

		foreach (['foodCode', 'foodName'] as $key)
		{
			if (!array_key_exists($key, $item) || !$this->IsImportScalar($item[$key]) || trim((string)$item[$key]) === '')
			{
				throw new \InvalidArgumentException('Invalid China Food item in ' . $file . ' at row ' . $rowIndex . ': missing ' . $key);
			}
		}
	}

	private function NullableNumber(array $item, $key, $file, $rowIndex)
	{
		if (!array_key_exists($key, $item) || $item[$key] === null || (is_string($item[$key]) && trim($item[$key]) === ''))
		{
			return null;
		}

		if (!$this->IsImportScalar($item[$key]) || !is_numeric($item[$key]))
		{
			throw new \InvalidArgumentException('Invalid numeric nutrient ' . $key . ' in ' . $file . ' at row ' . $rowIndex . ' (foodCode=' . $item['foodCode'] . ', foodName=' . $item['foodName'] . ')');
		}

		return (float)$item[$key];
	}

	private function IsImportScalar($value)
	{
		return is_scalar($value) && !is_bool($value);
	}

	private function CategoryFromFilename($file)
	{
		$filename = basename($file);
		if (str_starts_with($filename, 'merged_'))
		{
			$filename = substr($filename, strlen('merged_'));
		}

		return preg_replace('/\.json$/', '', $filename);
	}
}
