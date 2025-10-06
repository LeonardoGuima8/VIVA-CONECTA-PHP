<?php
namespace App\Config;

class Config
{
    private array $items = [];

    public function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $target =& $this->items;
        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target =& $target[$segment];
        }
        $target = $value;
    }

    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $target = $this->items;
        foreach ($segments as $segment) {
            if (!is_array($target) || !array_key_exists($segment, $target)) {
                return $default;
            }
            $target = $target[$segment];
        }
        return $target;
    }

    public function all(): array
    {
        return $this->items;
    }
}
