<?php

if (version_compare(PHP_VERSION, '5.6') < 0) {
    throw new \Exception('scssphp requires PHP 5.6 or above');
}

if (! class_exists('MMMScssPhp\ScssPhp\Version')) {
    spl_autoload_register(function ($class) {
        if (0 !== strpos($class, 'MMMScssPhp\ScssPhp\\')) {
            // Not a ScssPhp class
            return;
        }

        $subClass = substr($class, strlen('MMMScssPhp\ScssPhp\\'));
        $path = __DIR__ . '/src/' . str_replace('\\', '/', $subClass) . '.php';

        if (file_exists($path)) {
            require $path;
        }
    });
}
