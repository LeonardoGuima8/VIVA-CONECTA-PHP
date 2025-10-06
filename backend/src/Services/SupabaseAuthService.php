<?php
namespace App\Services;

use App\Config\Env;
use App\Exceptions\SupabaseException;
use App\Support\HttpClient;

class SupabaseAuthService
{
    private string $authUrl;
    private string $serviceKey;
    private string $anonKey;
    private HttpClient $client;

    public function __construct(?HttpClient $client = null)
    {
        $base = rtrim((string) Env::get('SUPABASE_URL', ''), '/');
        $this->authUrl = $base === '' ? '' : $base . '/auth/v1';
        $this->serviceKey = (string) Env::get('SUPABASE_SERVICE_ROLE_KEY', '');
        $this->anonKey = (string) Env::get('SUPABASE_ANON_KEY', '');
        $this->client = $client ?? new HttpClient();
    }

    public function ready(): bool
    {
        return $this->authUrl !== '' && ($this->serviceKey !== '' || $this->anonKey !== '');
    }

    public function signUp(string $email, string $password, array $metadata = []): array
    {
        $payload = [
            'email' => strtolower($email),
            'password' => $password,
            'data' => $metadata,
        ];
        $response = $this->request('POST', '/signup', $payload, false);
        return $this->parseResponse($response);
    }

    public function signInWithPassword(string $email, string $password): array
    {
        $payload = [
            'email' => strtolower($email),
            'password' => $password,
        ];
        $response = $this->request('POST', '/token?grant_type=password', $payload, false, ['Prefer' => 'return=minimal']);
        return $this->parseResponse($response);
    }

    public function signInWithOtp(string $phoneOrEmail, bool $isPhone = false): array
    {
        $payload = $isPhone ? ['phone' => $phoneOrEmail] : ['email' => strtolower($phoneOrEmail)];
        $response = $this->request('POST', $isPhone ? '/otps' : '/otp', $payload, false);
        return $this->parseResponse($response);
    }

    public function getUser(string $accessToken): array
    {
        $response = $this->request('GET', '/user', null, false, ['Authorization' => 'Bearer ' . $accessToken]);
        return $this->parseResponse($response);
    }

    public function adminCreateUser(array $payload): array
    {
        $response = $this->request('POST', '/admin/users', $payload, true);
        return $this->parseResponse($response);
    }

    private function request(string $method, string $path, $payload = null, bool $useServiceKey = false, array $headers = []): array
    {
        if (!$this->ready()) {
            throw new SupabaseException('Supabase Auth configuration missing', 500);
        }

        $authKey = $useServiceKey ? $this->serviceKey : ($this->serviceKey !== '' ? $this->serviceKey : $this->anonKey);
        $headers = array_merge([
            'apikey' => $authKey,
            'Content-Type' => 'application/json',
        ], $headers);

        if ($useServiceKey) {
            $headers['Authorization'] = 'Bearer ' . $this->serviceKey;
        } elseif (!isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer ' . $authKey;
        }

        $url = $this->authUrl . $path;
        return $this->client->request($method, $url, $headers, $payload);
    }

    private function parseResponse(array $response): array
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
            throw new SupabaseException('Supabase Auth error: ' . $error, $status);
        }

        if ($status >= 400) {
            $message = is_array($decoded) && isset($decoded['msg']) ? $decoded['msg'] : 'Supabase Auth request failed';
            throw new SupabaseException($message, $status, is_array($decoded) ? $decoded : ['raw' => $body]);
        }

        return ['status' => $status, 'data' => $decoded];
    }
}
