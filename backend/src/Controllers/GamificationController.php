<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\GamificationService;
use InvalidArgumentException;

class GamificationController extends ApiController
{
    private GamificationService $gamification;

    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function summary(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return $this->json(['error' => 'user_id is required'], 422);
        }
        $summary = $this->gamification->summary($userId);
        return $this->json(['data' => $summary]);
    }

    public function register(Request $request)
    {
        try {
            $event = $this->gamification->registerEvent($request->input());
            return $this->created(['data' => $event]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
