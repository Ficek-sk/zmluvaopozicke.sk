<?php

/**
 * Compiles every .latte template under app/ with the Latte engine the application uses
 * (same extensions, filters and functions as at runtime) and reports templates that fail
 * to compile or call a filter/function the engine does not know.
 *
 * Usage: php bin/latte-lint.php [path ...]
 * Exit code 1 when any template fails.
 */

use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Utils\Finder;

$container = require __DIR__ . '/../app/bootstrap.php';

/** @var TemplateFactory $templateFactory */
$templateFactory = $container->getByType(TemplateFactory::class);

$paths = array_slice($argv, 1) ?: [__DIR__ . '/../app'];
$files = [];
foreach ($paths as $path) {
	if (is_file($path)) {
		$files[] = realpath($path);
		continue;
	}
	foreach (Finder::findFiles('*.latte')->from($path) as $file) {
		$files[] = $file->getRealPath();
	}
}
sort($files);

$failed = 0;
foreach ($files as $file) {
	/** @var Template $template */
	$template = $templateFactory->createTemplate();
	$latte = $template->getLatte();
	$latte->setLoader(new Latte\Loaders\FileLoader);

	try {
		$code = $latte->compile($file);
	} catch (Throwable $e) {
		$failed++;
		echo "FAIL $file\n     " . get_class($e) . ': ' . $e->getMessage() . "\n";
		continue;
	}

	$problems = [];
	$filters = array_change_key_case($latte->getFilters());
	// {block|filter} compiles to filterContent('filter', ...), every other filter to ->filter
	preg_match_all('#\$this->filters->(?:filterContent\(\'(\w+)\'|(?!filterContent\b)(\w+))#', $code, $m);
	foreach (array_unique(array_filter(array_merge($m[1], $m[2]))) as $filter) {
		if (!isset($filters[strtolower($filter)])) {
			$problems[] = "unknown filter |$filter";
		}
	}

	$functions = array_change_key_case($latte->getFunctions());
	preg_match_all('#\$this->global->fn->(\w+)#', $code, $m);
	foreach (array_unique($m[1]) as $function) {
		if (!isset($functions[strtolower($function)])) {
			$problems[] = "unknown function $function()";
		}
	}

	if ($problems) {
		$failed++;
		echo "FAIL $file\n     " . implode("\n     ", $problems) . "\n";
	}
}

echo count($files) . ' templates, ' . $failed . " failed\n";
exit($failed ? 1 : 0);
