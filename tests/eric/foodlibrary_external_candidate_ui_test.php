<?php

function check_contains($source, $needle, $message)
{
	if (strpos($source, $needle) === false)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

function check_same($actual, $expected, $message)
{
	if ($actual !== $expected)
	{
		fwrite(STDERR, $message . "\n");
		fwrite(STDERR, "expected: " . $expected . "\n");
		fwrite(STDERR, "actual: " . $actual . "\n");
		exit(1);
	}
}

function check_match($pattern, $source, $message)
{
	if (preg_match($pattern, $source) !== 1)
	{
		fwrite(STDERR, $message . "\n");
		exit(1);
	}
}

function extract_js_function($source, $name)
{
	$start = strpos($source, 'function ' . $name . '(');
	if ($start === false)
	{
		fwrite(STDERR, 'missing ' . $name . " function\n");
		exit(1);
	}

	$openBrace = strpos($source, '{', $start);
	if ($openBrace === false)
	{
		fwrite(STDERR, 'malformed ' . $name . " function\n");
		exit(1);
	}

	$depth = 0;
	$length = strlen($source);
	for ($i = $openBrace; $i < $length; $i++)
	{
		if ($source[$i] === '{')
		{
			$depth++;
		}
		elseif ($source[$i] === '}')
		{
			$depth--;
			if ($depth === 0)
			{
				return substr($source, $start, $i - $start + 1);
			}
		}
	}

	fwrite(STDERR, 'unterminated ' . $name . " function\n");
	exit(1);
}

function extract_js_variable($source, $name)
{
	$start = strpos($source, 'var ' . $name . ' = ');
	if ($start === false)
	{
		fwrite(STDERR, 'missing ' . $name . " variable\n");
		exit(1);
	}

	$openBracket = strpos($source, '[', $start);
	if ($openBracket === false)
	{
		fwrite(STDERR, 'malformed ' . $name . " variable\n");
		exit(1);
	}

	$depth = 0;
	$length = strlen($source);
	for ($i = $openBracket; $i < $length; $i++)
	{
		if ($source[$i] === '[' || $source[$i] === '{' || $source[$i] === '(')
		{
			$depth++;
		}
		elseif ($source[$i] === ']' || $source[$i] === '}' || $source[$i] === ')')
		{
			$depth--;
			if ($depth === 0)
			{
				$semicolon = strpos($source, ';', $i);
				if ($semicolon === false)
				{
					fwrite(STDERR, 'unterminated ' . $name . " variable\n");
					exit(1);
				}

				return substr($source, $start, $semicolon - $start + 1);
			}
		}
	}

	fwrite(STDERR, 'unterminated ' . $name . " variable\n");
	exit(1);
}

function run_node_render_contract($viewJs, $headerCount)
{
	$functions = [
		'FoodLibraryEscape',
		'FoodLibraryIsExternalCandidate',
		'FoodLibraryRenderName',
		'FoodLibraryRenderExternalAction',
		'FoodLibraryRenderLocalAction',
		'FoodLibraryRenderAction',
		'FoodLibraryParseAliases'
	];

	$script = <<<'JS'
String.prototype.escapeHTML = function ()
{
	return this.replace(/[&<>"'`=\/]/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;' })[s]);
};

function __t(text)
{
	return text;
}

function U(path)
{
	return path;
}

JS;

	foreach ($functions as $function)
	{
		$script .= extract_js_function($viewJs, $function) . "\n\n";
	}
	$script .= extract_js_variable($viewJs, 'FoodLibraryColumns') . "\n\n";

	$script .= <<<'JS'
function check(condition, message)
{
	if (!condition)
	{
		console.error(message);
		process.exit(1);
	}
}

function checkSame(actual, expected, message)
{
	if (actual !== expected)
	{
		console.error(message);
		console.error('expected:', expected);
		console.error('actual:', actual);
		process.exit(1);
	}
}

const externalRow = {
	imported: false,
	id: null,
	name: '<img src=x onerror=alert("x")>',
	source: {
		provider: 'boo"hee space 雪',
		external_id: "id ' 42 雪"
	}
};

const externalName = FoodLibraryRenderName(externalRow.name, 'display', externalRow);
check(externalName.includes('&lt;img src&#x3D;x onerror&#x3D;alert(&quot;x&quot;)&gt;'), 'external candidate name should be HTML escaped');
check(!externalName.includes('<img'), 'external candidate name should not include raw HTML');
check(externalName.includes('<span class="badge badge-info">Boohee</span>'), 'external candidate should include Boohee badge');
check(!externalName.includes('/product/'), 'external candidate name should not link to product URL');
checkSame(FoodLibraryRenderName(externalRow.name, 'sort', externalRow), externalRow.name, 'non-display name render should return raw data');

const action = FoodLibraryRenderExternalAction(externalRow, 'display');
check(action.includes('food-library-import-external'), 'external action should include import button class');
check(action.includes('data-provider="boo&quot;hee space 雪"'), 'provider attribute should escape quotes and preserve spaces/unicode');
check(action.includes('data-external-id="id &#39; 42 雪"'), 'external id attribute should escape quotes and preserve spaces/unicode');
check(!action.includes('data-provider="boo"hee'), 'provider attribute should not contain unescaped double quotes');
check(!action.includes("data-external-id=\"id '"), 'external id attribute should not contain unescaped single quotes');
checkSame(FoodLibraryRenderExternalAction(externalRow, 'sort'), '', 'non-display external action should be empty');
checkSame(FoodLibraryRenderExternalAction({ imported: true, id: 12, source: externalRow.source }, 'display'), '', 'local food action should be empty');
checkSame(FoodLibraryRenderAction(externalRow, 'display'), action, 'external render action should delegate to external action');

const localName = FoodLibraryRenderName('Local <Food>', 'display', { imported: true, id: 12 });
check(localName.includes('/product/12'), 'local food should link to product URL');
check(localName.includes('Local &lt;Food&gt;'), 'local food link text should be escaped');

const localAction = FoodLibraryRenderLocalAction({ imported: true, id: 12 }, 'display');
check(localAction.includes('food-library-edit-aliases'), 'local food should include alias edit button');
check(localAction.includes('data-product-id="12"'), 'local alias edit action should include product id');
check(localAction.includes('Aliases'), 'local alias edit action should include button text');
checkSame(FoodLibraryRenderLocalAction({ imported: true, id: 12 }, 'sort'), '', 'non-display local action should be empty');
checkSame(FoodLibraryRenderLocalAction(externalRow, 'display'), '', 'external row should not include local alias edit action');
checkSame(FoodLibraryRenderAction({ imported: true, id: 12 }, 'display'), localAction, 'local render action should delegate to local action');

const parsedAliases = FoodLibraryParseAliases(`番茄
西红柿, 番茄
`);
checkSame(JSON.stringify(parsedAliases), JSON.stringify(['番茄', '西红柿']), 'aliases parser should split, trim, and dedupe aliases');

checkSame(FoodLibraryColumns.length, Number(process.env.FOOD_LIBRARY_HEADER_COUNT), 'FoodLibraryColumns length should match Blade table header count');

console.log('foodlibrary external render contract ok');
JS;

	$tmpFile = tempnam(sys_get_temp_dir(), 'foodlibrary-render-');
	if ($tmpFile === false)
	{
		fwrite(STDERR, "failed to create temp node test file\n");
		exit(1);
	}

	file_put_contents($tmpFile, $script);
	$output = [];
	$exitCode = 0;
	exec('FOOD_LIBRARY_HEADER_COUNT=' . escapeshellarg((string)$headerCount) . ' node ' . escapeshellarg($tmpFile) . ' 2>&1', $output, $exitCode);
	unlink($tmpFile);

	if ($exitCode !== 0)
	{
		fwrite(STDERR, implode("\n", $output) . "\n");
		exit(1);
	}
}

$viewJs = file_get_contents(__DIR__ . '/../../public/viewjs/foodlibrary.js');
if ($viewJs === false)
{
	fwrite(STDERR, "failed to read public/viewjs/foodlibrary.js\n");
	exit(1);
}

$view = file_get_contents(__DIR__ . '/../../views/foodlibrary.blade.php');
if ($view === false)
{
	fwrite(STDERR, "failed to read views/foodlibrary.blade.php\n");
	exit(1);
}

check_contains($viewJs, 'FoodLibraryIsExternalCandidate', 'missing FoodLibraryIsExternalCandidate helper');
check_contains($viewJs, 'FoodLibraryImportExternalCandidate', 'missing FoodLibraryImportExternalCandidate helper');
check_contains($viewJs, 'FoodLibraryRenderName', 'missing FoodLibraryRenderName helper');
check_contains($viewJs, 'FoodLibraryRenderExternalAction', 'missing FoodLibraryRenderExternalAction helper');
check_contains($viewJs, 'FoodLibraryRenderLocalAction', 'missing FoodLibraryRenderLocalAction helper');
check_contains($viewJs, 'FoodLibraryRenderAction', 'missing FoodLibraryRenderAction helper');
check_contains($viewJs, 'FoodLibraryEditAliases', 'missing FoodLibraryEditAliases helper');
check_contains($viewJs, 'FoodLibrarySaveAliases', 'missing FoodLibrarySaveAliases helper');
check_contains($viewJs, 'FoodLibraryParseAliases', 'missing FoodLibraryParseAliases helper');
check_contains($viewJs, 'eric/foods/import-from-source', 'missing import-from-source API call');
check_contains($viewJs, 'eric/foods/" + encodeURIComponent(productId) + "/aliases', 'missing alias update API call');
check_contains($viewJs, 'data-provider', 'missing provider data attribute');
check_contains($viewJs, 'data-external-id', 'missing external id data attribute');
check_contains($viewJs, 'data-product-id', 'missing alias edit product id data attribute');
check_contains($viewJs, 'food-library-import-external', 'missing external import button class');
check_contains($viewJs, 'food-library-edit-aliases', 'missing alias edit button class');
check_contains($viewJs, 'FoodLibraryIsExternalCandidate(row)', 'missing guard preventing product links for external candidates');
check_contains($view, "{{ \$__t('Actions') }}", 'missing Actions table header');
check_contains($view, 'food-library-aliases-modal', 'missing aliases modal');
check_contains($view, 'food-library-aliases-input', 'missing aliases textarea');
check_contains($view, 'food-library-aliases-save', 'missing aliases save button');
check_contains($viewJs, 'FoodLibraryRenderAction(row, type)', 'action column should use FoodLibraryRenderAction');
check_contains($viewJs, 'FoodLibraryRenderName(data, type, row)', 'name column should use FoodLibraryRenderName');
check_contains($viewJs, 'var FoodLibraryColumns = [', 'missing FoodLibraryColumns array');
check_contains($viewJs, '"columns": FoodLibraryColumns', 'DataTables should use FoodLibraryColumns');
check_contains($viewJs, 'FoodLibraryEscape(row.source.provider)', 'provider attribute should use FoodLibraryEscape');
check_contains($viewJs, 'FoodLibraryEscape(row.source.external_id)', 'external id attribute should use FoodLibraryEscape');
check_match('/function FoodLibraryRenderName[\s\S]*FoodLibraryIsExternalCandidate\(row\)[\s\S]*return \'<a href="/', $viewJs, 'external candidate branch should appear before product link branch');
check_match('/function FoodLibraryRenderName[\s\S]*FoodLibraryIsExternalCandidate\(row\)[\s\S]*Boohee[\s\S]*return \'<a href="/', $viewJs, 'external branch should render badge before product link fallback');

$headerCount = preg_match_all('/<th\b/', $view);
run_node_render_contract($viewJs, $headerCount);

echo "foodlibrary external UI contract ok\n";
