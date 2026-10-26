<?php
require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(false);
$configurator->enableDebugger(__DIR__ . '/../log');
// PHP 7.4 deprecates ReflectionType::__toString(), used by Nette 2.4 and Doctrine 2.5 on every request;
// Tracy enables E_ALL and would send each one to Sentry as an error, so deprecations stay silent until the PHP 8 upgrade.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

return $container;
