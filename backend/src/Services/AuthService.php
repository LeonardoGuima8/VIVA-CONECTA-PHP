<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Support\Validator;
use App\Exceptions\SupabaseException;

class AuthService
{
    private UserRepository $users;
    private SupabaseAuthService $auth;
    private SupabaseService $supabase;

    public function __construct(UserRepository $users, SupabaseAuthService $auth, SupabaseService $supabase)
    {
        $this->users = $users;
        $this->auth = $auth;
        $this->supabase = $supabase;
    }

    public function register(array $input): array
    {
        Validator::require($input, ['email', 'password', 'name', 'role']);
        $role = $input['role'];
        if (!in_array($role, ['patient', 'professional', 'clinic', 'admin'], true)) {
            throw new SupabaseException('Invalid role provided', 422);
        }

        $metadata = [
            'name' => $input['name'],
            'role' => $role,
            'phone' => $input['phone'] ?? null,
        ];

        $authResponse = $this->auth->adminCreateUser([
            'email' => strtolower($input['email']),
            'password' => $input['password'],
            'email_confirm' => true,
            'user_metadata' => $metadata,
        ]);

        $userData = $authResponse['data'] ?? [];
        $userId = $userData['id'] ?? null;
        if (!$userId) {
            throw new SupabaseException('Failed to create user in Supabase Auth', 500, $userData);
        }

        $record = [
            'id' => $userId,
            'email' => strtolower($input['email']),
            'name' => $input['name'],
            'phone' => $input['phone'] ?? null,
            'role' => $role,
        ];

        $this->supabase->upsert('users', [$record]);
        if ($role === 'professional') {
            $this->supabase->upsert('professionals', [[
                'user_id' => $userId,
                'council_type' => $input['council_type'] ?? null,
                'council_id' => $input['council_id'] ?? null,
                'years_experience' => $input['years_experience'] ?? 0,
            ]]);
        }

        if ($role === 'clinic') {
            $this->supabase->upsert('clinics', [[
                'user_id' => $userId,
                'trade_name' => $input['trade_name'] ?? null,
                'legal_name' => $input['legal_name'] ?? null,
                'cnpj' => $input['cnpj'] ?? null,
            ]]);
        }

        return [
            'user_id' => $userId,
            'email' => $input['email'],
            'role' => $role,
        ];
    }

    public function login(array $input): array
    {
        Validator::require($input, ['email', 'password']);
        $authResponse = $this->auth->signInWithPassword($input['email'], $input['password']);
        return $authResponse['data'] ?? [];
    }

    public function profile(string $accessToken): array
    {
        $userResponse = $this->auth->getUser($accessToken);
        return $userResponse['data'] ?? [];
    }
}
