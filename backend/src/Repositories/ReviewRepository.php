<?php
namespace App\Repositories;

class ReviewRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'reviews');
    }

    public function commitments(string $appointmentId): array
    {
        return $this->supabase->select('review_commitments', [
            'filter' => ['appointment_id' => 'eq.' . $appointmentId],
            'limit' => 1,
        ]);
    }
}
