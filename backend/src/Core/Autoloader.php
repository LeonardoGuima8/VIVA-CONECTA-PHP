<?php
namespace App\Core;

class Autoloader
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load(string $class): void
    {
        if (strpos($class, 'App\\') !== 0) {
            return;
        }

        $relative = substr($class, 4);
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $file = $this->baseDir . $relativePath;

        if (is_file($file)) {
            require_once $file;
        }
    }
}
