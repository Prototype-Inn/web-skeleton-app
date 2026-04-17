<?php

declare(strict_types=1);

$routes = [];

// Include route groups
$routes = array_merge($routes, require __DIR__ . '/routes_mvc.php');
$routes = array_merge($routes, require __DIR__ . '/routes_ajax.php');
$routes = array_merge($routes, require __DIR__ . '/routes_api.php');

return $routes;
