<?php
namespace App\Services;

use App\Repositories\ReviewRepository;
use App\Support\Validator;

class ReviewService
{
    private ReviewRepository $reviews;
    private SupabaseService $supabase;

    public function __construct(ReviewRepository $reviews, SupabaseService $supabase)
    {
        $this->reviews = $reviews;
        $this->supabase = $supabase;
    }

    public function commit(string $appointmentId, string $userId): array
    {
        $payload = [
            'appointment_id' => $appointmentId,
            'shown_at' => date('c'),
            'accepted' => true,
        ];
        $this->supabase->upsert('review_commitments', [$payload]);
        return $payload;
    }

    public function create(array $input): array
    {
        Validator::require($input, ['appointment_id', 'rater_id', 'rating']);
        $record = [
            'appointment_id' => $input['appointment_id'],
            'rater_id' => $input['rater_id'],
            'rating' => $input['rating'],
            'dimensions' => $input['dimensions'] ?? null,
            'comment' => $input['comment'] ?? null,
            'published_at' => date('c'),
        ];

        $result = $this->supabase->insert('reviews', [$record]);
        $review = $result['data'][0] ?? [];

        $this->supabase->callFunction('ranking_recalculate', [
            'appointment_id' => $input['appointment_id'],
        ]);

        return $review;
    }

    public function listForProfessional(string $professionalId): array
    {
        $response = $this->supabase->select('reviews_view', [
            'filter' => ['professional_id' => 'eq.' . $professionalId],
            'order' => [['column' => 'published_at', 'ascending' => false]],
        ]);
        return $response['data'] ?? [];
    }
}
