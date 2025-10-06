<?php
namespace App\Core;

use App\Exceptions\SupabaseException;
use App\Http\Request;
use App\Http\Response;
use InvalidArgumentException;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(): void
    {
        $request = Request::fromGlobals();
        $method = $request->method();
        $uri = $request->path();

        foreach ($this->routes as $route) {
            $pattern = '@^' . preg_replace('@:([a-zA-Z_][a-zA-Z0-9_]*)@', '(?P<$1>[^/]+)', $route['path']) . '$@';
            if ($method === $route['method'] && preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                try {
                    $response = $this->invoke($route['handler'], $request, $params);
                } catch (SupabaseException $e) {
                    $response = Response::json([
                        'error' => 'supabase_error',
                        'message' => $e->getMessage(),
                        'details' => $e->details(),
                    ], $e->getCode() ?: 500);
                } catch (InvalidArgumentException $e) {
                    $response = Response::json([
                        'error' => 'validation_error',
                        'message' => $e->getMessage(),
                    ], 422);
                } catch (\Throwable $e) {
                    $response = Response::json([
                        'error' => 'internal_error',
                        'message' => $e->getMessage(),
                    ], 500);
                }

                $this->send($response);
                return;
            }
        }

        $this->send(Response::json(['error' => 'not_found'], 404));
    }

    private function invoke(callable $handler, Request $request, array $params)
    {
        $reflection = $this->getReflection($handler);
        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            $name = $parameter->getName();

            if ($type && !$type->isBuiltin() && $type->getName() === Request::class) {
                $arguments[] = $request;
                continue;
            }

            if ($type && $type->getName() === 'array') {
                $arguments[] = $params;
                continue;
            }

            if ($name === 'request') {
                $arguments[] = $request;
                continue;
            }

            if (in_array($name, ['params', 'parameters', 'routeParams'], true)) {
                $arguments[] = $params;
                continue;
            }

            $arguments[] = $params;
        }

        if (is_array($handler)) {
            $object = $handler[0];
            if (empty($arguments) && $reflection->getNumberOfParameters() === 0) {
                $result = $reflection->invoke($object);
            } else {
                $result = $reflection->invokeArgs($object, $arguments);
            }
        } else {
            if (empty($arguments) && $reflection->getNumberOfParameters() === 0) {
                $result = $reflection->invoke();
            } else {
                $result = $reflection->invokeArgs($arguments);
            }
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::json(['data' => $result]);
    }

    private function getReflection(callable $handler)
    {
        if (is_array($handler)) {
            return new \ReflectionMethod($handler[0], $handler[1]);
        }

        return new \ReflectionFunction($handler);
    }

    private function send($response): void
    {
        if ($response instanceof Response) {
            $response->send();
            return;
        }

        Response::json($response)->send();
    }
}
