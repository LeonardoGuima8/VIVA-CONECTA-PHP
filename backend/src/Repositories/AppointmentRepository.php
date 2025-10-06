<?php
namespace App\Repositories;

class AppointmentRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'appointments');
    }

    public function events(string $appointmentId): array
    {
        return $this->supabase->select('appointment_events', [
            'filter' => ['appointment_id' => 'eq.' . $appointmentId],
            'order' => [['column' => 'created_at']],
        ]);
    }
}
