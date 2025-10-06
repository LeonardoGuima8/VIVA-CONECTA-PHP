<?php
namespace App\Http;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $headers;

    public static function fromGlobals(): self
    {
        $server = $_SERVER;
        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $uri = $server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = $_GET ?? [];

        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders() ?: [];
        } else {
            foreach ($server as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$name] = $value;
                }
            }
        }

        $raw = file_get_contents('php://input');
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        $body = [];

        if ($raw !== false && $raw !== '') {
            if (str_contains($contentType, 'application/json')) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $body = $decoded;
                }
            } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($raw, $body);
            }
        }

        $request = new self($method, $path, $query, $body, $headers);
        return $request;
    }

    public function __construct(string $method, string $path, array $query = [], array $body = [], array $headers = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        $normalized = strtolower($key);
        foreach ($this->headers as $headerKey => $value) {
            if (strtolower($headerKey) === $normalized) {
                return $value;
            }
        }
        return $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
