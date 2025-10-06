<?php
namespace App\Services;

class DashboardService
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function patient(string $userId): array
    {
        $response = $this->supabase->rpc('dashboard_patient_summary', ['p_user_id' => $userId]);
        return $response['data'] ?? [];
    }

    public function professional(string $userId): array
    {
        $response = $this->supabase->rpc('dashboard_professional_summary', ['p_user_id' => $userId]);
        return $response['data'] ?? [];
    }

    public function admin(): array
    {
        $response = $this->supabase->rpc('dashboard_admin_summary', []);
        return $response['data'] ?? [];
    }
}
