<?php
namespace App\Services;

use App\Repositories\ProfessionalRepository;
use App\Services\SupabaseService;

class ProfessionalService
{
    private ProfessionalRepository $professionals;
    private SupabaseService $supabase;

    public function __construct(ProfessionalRepository $professionals, SupabaseService $supabase)
    {
        $this->professionals = $professionals;
        $this->supabase = $supabase;
    }

    public function detail(string $id): array
    {
        $response = $this->supabase->select('professionals_view', [
            'filter' => ['id' => 'eq.' . $id],
            'limit' => 1,
        ]);
        return $response['data'][0] ?? [];
    }

    public function availability(string $id, ?string $from = null, ?string $to = null): array
    {
        $response = $this->professionals->availability($id, $from, $to);
        return $response['data'] ?? [];
    }
}
