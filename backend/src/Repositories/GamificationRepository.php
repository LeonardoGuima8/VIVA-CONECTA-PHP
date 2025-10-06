<?php
namespace App\Repositories;

class GamificationRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'gamification_events');
    }

    public function summary(string $userId): array
    {
        return $this->supabase->rpc('gamification_summary', ['p_user_id' => $userId]);
    }
}
