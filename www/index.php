<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

if (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS']) {
  $_SERVER['SERVER_PORT'] = 443;
}

// if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
//     if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' && isset($_SERVER['SERVER_PORT']) && in_array($_SERVER['SERVER_PORT'], [80])) { // https over proxy
//         $_SERVER['HTTPS'] = 'On';
//         $_SERVER['SERVER_PORT'] = 443;
//
//     } elseif ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http' && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 80) { // http over proxy
//         $_SERVER['HTTPS'] = 'Off';
//         $_SERVER['SERVER_PORT'] = 80;
//     }
// }

// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/../app/bootstrap.php';

$container->getByType(Nette\Application\Application::class)
    ->run();
