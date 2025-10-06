<?php
namespace App\Http;

class Response
{
    private array $headers;
    private int $status;
    private $body;

    public function __construct($body, int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function json($data, int $status = 200, array $headers = []): self
    {
        $headers = array_merge(['Content-Type' => 'application/json'], $headers);
        return new self($data, $status, $headers);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        if ($this->headers['Content-Type'] ?? '' === 'application/json') {
            echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo (string) $this->body;
    }
}
