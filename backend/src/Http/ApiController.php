<?php
namespace App\Http;

abstract class ApiController
{
    protected function json($data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    protected function created($data): Response
    {
        return $this->json($data, 201);
    }

    protected function noContent(): Response
    {
        return new Response('', 204);
    }
}
