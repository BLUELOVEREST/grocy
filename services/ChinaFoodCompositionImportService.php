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

		$imported = 0;
		foreach ($files as $file)
		{
			$items = json_decode(file_get_contents($file), true);
			if (!is_array($items) || json_last_error() !== JSON_ERROR_NONE)
			{
				throw new \InvalidArgumentException('Invalid JSON file: ' . $file);
			}

			$category = $this->CategoryFromFilename($file);
			foreach ($items as $item)
			{
				FoodLibraryService::GetInstance()->ImportFood([
					'provider' => 'china-food-composition',
					'external_id' => (string)$item['foodCode'],
					'name' => (string)$item['foodName'],
					'description' => $category,
					'category' => $category,
					'stock_unit' => 'g',
					'basis_amount' => 100,
					'basis_unit' => 'g',
					'calories' => $this->NullableNumber($item, 'energyKCal'),
					'protein' => $this->NullableNumber($item, 'protein'),
					'fat' => $this->NullableNumber($item, 'fat'),
					'carbohydrates' => $this->NullableNumber($item, 'CHO'),
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

	private function NullableNumber(array $item, $key)
	{
		if (!array_key_exists($key, $item) || $item[$key] === '' || $item[$key] === null)
		{
			return null;
		}

		return (float)$item[$key];
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
