<?php

namespace App\Modules\DI;

use Nette\DI\CompilerExtension;

/**
 * Brings back the "run" service tag that nette/di 3 dropped: every service tagged
 * `run` is created right after the container, so its setup calls (e.g. registering
 * a form extension method) happen before any presenter runs.
 */
class RunTagExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		foreach (array_keys($this->getContainerBuilder()->findByTag('run')) as $name) {
			$this->initialization->addBody('$this->getService(?);', [$name]);
		}
	}
}
