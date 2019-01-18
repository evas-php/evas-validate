<?php
/**
* Autoload.
*/

$loadDirs = [
    __DIR__ . '/../src/', // src directory
    __DIR__ . '/', // helpers directory
];
spl_autoload_register(function ($className) use ($loadDirs) {
    foreach ($loadDirs as $dir) {
        $filename = $dir . basename($className) . '.php';
        if (is_readable($filename)) {
            include $filename;
        }
    }
});
