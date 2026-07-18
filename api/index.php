<?php

declare(strict_types=1);

$runtimeDirectories = [
    '/tmp/views',
];

foreach ($runtimeDirectories as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$runtimePaths = [
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'VIEW_COMPILED_PATH' => '/tmp/views',
];

foreach ($runtimePaths as $name => $path) {
    if (getenv($name) === false) {
        putenv($name.'='.$path);
        $_ENV[$name] = $path;
        $_SERVER[$name] = $path;
    }
}

require dirname(__DIR__).'/public/index.php';
