<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\NotificationService;

class NotificationsController extends ApiController
{
    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return $this->json(['error' => 'user_id is required'], 422);
        }
        $items = $this->notifications->listForUser($userId);
        return $this->json(['data' => $items]);
    }

    public function test(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->query('user_id');
        if (!$userId) {
            return $this->json(['error' => 'user_id is required'], 422);
        }
        $notification = $this->notifications->sendTest($userId);
        return $this->created(['data' => $notification]);
    }

    public function whatsappWebhook(Request $request)
    {
        $payload = $request->input();
        $result = $this->notifications->processWhatsApp($payload);
        return $this->json($result);
    }
}
