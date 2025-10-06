<?php
namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Support\Validator;

class AppointmentService
{
    private AppointmentRepository $appointments;
    private SupabaseService $supabase;

    public function __construct(AppointmentRepository $appointments, SupabaseService $supabase)
    {
        $this->appointments = $appointments;
        $this->supabase = $supabase;
    }

    public function create(array $input): array
    {
        Validator::require($input, ['patient_id', 'professional_id', 'scheduled_at', 'channel']);
        $record = [
            'patient_id' => $input['patient_id'],
            'professional_id' => $input['professional_id'],
            'clinic_id' => $input['clinic_id'] ?? null,
            'type' => $input['type'] ?? 'presencial',
            'scheduled_at' => $input['scheduled_at'],
            'channel' => $input['channel'],
            'status' => 'pending',
            'notes' => $input['notes'] ?? null,
        ];

        $result = $this->supabase->insert('appointments', [$record]);
        $appointment = $result['data'][0] ?? [];

        if (!empty($appointment['id'])) {
            $this->supabase->insert('appointment_events', [[
                'appointment_id' => $appointment['id'],
                'event_type' => 'created',
                'payload' => $record,
            ]]);

            $this->supabase->callFunction('notifications_dispatcher', [
                'type' => 'appointment_created',
                'payload' => [
                    'appointment_id' => $appointment['id'],
                    'patient_id' => $input['patient_id'],
                    'professional_id' => $input['professional_id'],
                ],
            ]);
        }

        return $appointment;
    }

    public function update(string $id, array $changes): array
    {
        $result = $this->appointments->update($id, $changes);
        $updated = $result['data'][0] ?? [];

        if ($updated) {
            $this->supabase->insert('appointment_events', [[
                'appointment_id' => $id,
                'event_type' => 'updated',
                'payload' => $changes,
            ]]);
        }

        return $updated;
    }

    public function checkin(string $id, string $method = 'qr'): array
    {
        $payload = [
            'appointment_id' => $id,
            'method' => $method,
            'verified_at' => date('c'),
        ];
        $this->supabase->upsert('checkins', [$payload]);
        $this->supabase->insert('appointment_events', [[
            'appointment_id' => $id,
            'event_type' => 'checkin',
            'payload' => $payload,
        ]]);

        return $payload;
    }

    public function timeline(string $id): array
    {
        $events = $this->appointments->events($id);
        return $events['data'] ?? [];
    }
}
