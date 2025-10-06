<?php
namespace App\Services;

use App\Config\Env;
use App\Exceptions\SupabaseException;
use App\Support\HttpClient;

class SupabaseService
{
    private string $projectUrl;
    private string $restUrl;
    private string $serviceKey;
    private string $anonKey;
    private HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $this->projectUrl = rtrim((string) Env::get('SUPABASE_URL', ''), '/');
        $this->serviceKey = (string) Env::get('SUPABASE_SERVICE_ROLE_KEY', '');
        $this->anonKey = (string) Env::get('SUPABASE_ANON_KEY', '');
        $this->restUrl = $this->projectUrl === '' ? '' : $this->projectUrl . '/rest/v1/';
        $this->client = $client ?? new HttpClient();
    }

    public function ready(): bool
    {
        return $this->projectUrl !== '' && ($this->serviceKey !== '' || $this->anonKey !== '');
    }

    public function select(string $table, array $options = []): array
    {
        $query = $this->buildQuery($options);
        $url = $this->restUrl . $table . ($query ? '?' . $query : '');
        return $this->handleResponse($this->send('GET', $url, null, $options['headers'] ?? []));
    }

    public function insert(string $table, array $payload, array $options = []): array
    {
        $url = $this->restUrl . $table;
        $headers = array_merge(['Prefer' => 'return=representation'], $options['headers'] ?? []);
        return $this->handleResponse($this->send('POST', $url, $payload, $headers));
    }

    public function upsert(string $table, array $payload, array $options = []): array
    {
        $url = $this->restUrl . $table;
        $headers = array_merge(['Prefer' => 'resolution=merge-duplicates,return=representation'], $options['headers'] ?? []);
        return $this->handleResponse($this->send('POST', $url, $payload, $headers));
    }

    public function update(string $table, array $filters, array $payload, array $options = []): array
    {
        $query = $this->buildFilterQuery($filters);
        $url = $this->restUrl . $table . ($query ? '?' . $query : '');
        $headers = array_merge(['Prefer' => 'return=representation'], $options['headers'] ?? []);
        return $this->handleResponse($this->send('PATCH', $url, $payload, $headers));
    }

    public function delete(string $table, array $filters): array
    {
        $query = $this->buildFilterQuery($filters);
        $url = $this->restUrl . $table . ($query ? '?' . $query : '');
        return $this->handleResponse($this->send('DELETE', $url));
    }

    public function rpc(string $fn, array $args = []): array
    {
        $url = $this->projectUrl . '/rest/v1/rpc/' . $fn;
        return $this->handleResponse($this->send('POST', $url, $args));
    }

    public function callFunction(string $name, array $payload = [], array $headers = []): array
    {
        $url = $this->projectUrl . '/functions/v1/' . $name;
        return $this->handleResponse($this->send('POST', $url, $payload, $headers));
    }

    private function send(string $method, string $url, $payload = null, array $headers = []): array
    {
        if (!$this->ready()) {
            throw new SupabaseException('Supabase configuration missing', 500);
        }

        $authKey = $this->serviceKey !== '' ? $this->serviceKey : $this->anonKey;
        $defaultHeaders = [
            'apikey' => $authKey,
            'Authorization' => 'Bearer ' . $authKey,
            'Content-Type' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        return $this->client->request($method, $url, $headers, $payload);
    }

    private function handleResponse(array $response): array
    {
        $status = $response['status'];
        $body = $response['body'];
        $error = $response['error'];

        $decoded = null;
        if ($body !== null && $body !== '') {
            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = $body;
            }
        }

        if ($error) {
            throw new SupabaseException('Supabase request error: ' . $error, $status, ['raw' => $body]);
        }

        if ($status >= 400) {
            $message = is_array($decoded) && isset($decoded['message']) ? $decoded['message'] : 'Supabase request failed';
            throw new SupabaseException($message, $status, is_array($decoded) ? $decoded : ['raw' => $body]);
        }

        return [
            'status' => $status,
            'data' => $decoded,
        ];
    }

    private function buildQuery(array $options): string
    {
        $query = [];
        $query['select'] = $options['select'] ?? '*';

        if (!empty($options['filter'])) {
            foreach ($options['filter'] as $column => $condition) {
                $query[$column] = $condition;
            }
        }

        if (!empty($options['or'])) {
            $query['or'] = '(' . trim($options['or'], '()') . ')';
        }

        if (!empty($options['and'])) {
            $query['and'] = '(' . trim($options['and'], '()') . ')';
        }

        if (isset($options['limit'])) {
            $query['limit'] = (int) $options['limit'];
        }

        if (isset($options['offset'])) {
            $query['offset'] = (int) $options['offset'];
        }

        if (isset($options['order'])) {
            $order = $options['order'];
            if (is_array($order)) {
                $parts = [];
                foreach ($order as $item) {
                    if (is_array($item)) {
                        $column = $item['column'] ?? '';
                        if ($column === '') {
                            continue;
                        }
                        $parts[] = $column . ((isset($item['ascending']) && !$item['ascending']) ? '.desc' : '.asc');
                    } else {
                        $parts[] = $item;
                    }
                }
                if (!empty($parts)) {
                    $query['order'] = implode(',', $parts);
                }
            } else {
                $query['order'] = $order;
            }
        }

        return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    private function buildFilterQuery(array $filters): string
    {
        $query = [];
        foreach ($filters as $column => $condition) {
            $query[$column] = $condition;
        }
        return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
