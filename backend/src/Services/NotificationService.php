<?php
namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    private NotificationRepository $notifications;
    private SupabaseService $supabase;

    public function __construct(NotificationRepository $notifications, SupabaseService $supabase)
    {
        $this->notifications = $notifications;
        $this->supabase = $supabase;
    }

    public function listForUser(string $userId): array
    {
        $response = $this->notifications->all([
            'filter' => ['user_id' => 'eq.' . $userId],
            'order' => [['column' => 'created_at', 'ascending' => false]],
        ]);
        return $response['data'] ?? [];
    }

    public function sendTest(string $userId): array
    {
        $notification = [
            'user_id' => $userId,
            'title' => 'Test notification',
            'body' => 'This is a test push notification',
            'channel' => 'app',
        ];
        $result = $this->supabase->insert('notifications', [$notification]);
        return $result['data'][0] ?? [];
    }

    public function processWhatsApp(array $payload): array
    {
        $this->supabase->callFunction('whatsapp_webhook_handler', $payload);
        return ['ok' => true];
    }
}
