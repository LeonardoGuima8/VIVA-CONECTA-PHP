<?php
namespace App\Services;

use App\Repositories\ProfessionalRepository;

class SearchService
{
    private SupabaseService $supabase;
    private ProfessionalRepository $professionals;

    public function __construct(SupabaseService $supabase, ProfessionalRepository $professionals)
    {
        $this->supabase = $supabase;
        $this->professionals = $professionals;
    }

    public function search(array $criteria): array
    {
        $results = $this->professionals->search($criteria);
        return $results['data'] ?? [];
    }

    public function specialties(?string $vertical = null): array
    {
        $options = [
            'order' => [['column' => 'name']],
        ];
        if ($vertical) {
            $options['filter']['vertical'] = 'eq.' . $vertical;
        }
        $response = $this->supabase->select('specialties', $options);
        return $response['data'] ?? [];
    }

    public function subspecialties(?string $specialtyId = null): array
    {
        $options = [
            'order' => [['column' => 'name']],
        ];
        if ($specialtyId) {
            $options['filter']['specialty_id'] = 'eq.' . $specialtyId;
        }
        $response = $this->supabase->select('subspecialties', $options);
        return $response['data'] ?? [];
    }
}
