<?php
namespace App\Services;

use App\Support\Validator;

class ChatService
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function createThread(array $input): array
    {
        Validator::require($input, ['appointment_id', 'participants']);
        $record = [
            'appointment_id' => $input['appointment_id'],
            'participants' => $input['participants'],
            'created_by' => $input['created_by'] ?? null,
        ];

        $result = $this->supabase->insert('chat_threads', [$record]);
        return $result['data'][0] ?? [];
    }

    public function postMessage(array $input): array
    {
        Validator::require($input, ['thread_id', 'sender_id', 'content']);
        $record = [
            'thread_id' => $input['thread_id'],
            'sender_id' => $input['sender_id'],
            'content' => $input['content'],
            'attachments' => $input['attachments'] ?? null,
        ];

        $result = $this->supabase->insert('chat_messages', [$record]);
        $message = $result['data'][0] ?? [];

        $this->supabase->callFunction('notifications_dispatcher', [
            'type' => 'chat_message',
            'payload' => $message,
        ]);

        return $message;
    }

    public function listMessages(string $threadId): array
    {
        $response = $this->supabase->select('chat_messages', [
            'filter' => ['thread_id' => 'eq.' . $threadId],
            'order' => [['column' => 'created_at']],
        ]);
        return $response['data'] ?? [];
    }
}
