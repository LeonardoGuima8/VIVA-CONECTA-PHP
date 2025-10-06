<?php
namespace App\Core;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function set(string $id, callable $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $this->instances[$id] = $this->bindings[$id]($this);
            return $this->instances[$id];
        }

        return $this->resolve($id);
    }

    private function resolve(string $id)
    {
        if (!class_exists($id)) {
            throw new RuntimeException("Class {$id} not found");
        }

        try {
            $reflection = new ReflectionClass($id);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class {$id} is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            $instance = new $id();
            $this->instances[$id] = $instance;
            return $instance;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException('Cannot resolve dependency: ' . $parameter->getName() . ' for ' . $id);
        }

        $instance = $reflection->newInstanceArgs($dependencies);
        $this->instances[$id] = $instance;
        return $instance;
    }
}
