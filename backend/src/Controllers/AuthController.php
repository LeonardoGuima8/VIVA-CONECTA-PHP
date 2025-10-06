<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\AuthService;
use InvalidArgumentException;

class AuthController extends ApiController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function register(Request $request)
    {
        try {
            $user = $this->auth->register($request->input());
            return $this->created(['data' => $user]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $session = $this->auth->login($request->input());
            return $this->json(['data' => $session]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function profile(Request $request)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return $this->json(['error' => 'Authorization header missing'], 401);
        }
        $token = str_replace('Bearer ', '', $token);
        $profile = $this->auth->profile($token);
        return $this->json(['data' => $profile]);
    }
}
