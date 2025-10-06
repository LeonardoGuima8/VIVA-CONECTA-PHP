<?php
namespace App\Services;

use App\Repositories\GamificationRepository;
use App\Support\Validator;

class GamificationService
{
    private GamificationRepository $repository;
    private SupabaseService $supabase;

    public function __construct(GamificationRepository $repository, SupabaseService $supabase)
    {
        $this->repository = $repository;
        $this->supabase = $supabase;
    }

    public function summary(string $userId): array
    {
        $summary = $this->repository->summary($userId);
        return $summary['data'] ?? [];
    }

    public function registerEvent(array $input): array
    {
        Validator::require($input, ['user_id', 'type']);
        $record = [
            'user_id' => $input['user_id'],
            'type' => $input['type'],
            'points' => $input['points'] ?? 0,
            'metadata' => $input['metadata'] ?? null,
        ];
        $result = $this->supabase->insert('gamification_events', [$record]);
        return $result['data'][0] ?? [];
    }
}
