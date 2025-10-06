<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\ChatService;
use InvalidArgumentException;

class ChatController extends ApiController
{
    private ChatService $chat;

    public function __construct(ChatService $chat)
    {
        $this->chat = $chat;
    }

    public function createThread(Request $request)
    {
        try {
            $thread = $this->chat->createThread($request->input());
            return $this->created(['data' => $thread]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function postMessage(Request $request)
    {
        try {
            $message = $this->chat->postMessage($request->input());
            return $this->json(['data' => $message], 201);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function listMessages(Request $request, array $params)
    {
        $messages = $this->chat->listMessages($params['id']);
        return $this->json(['data' => $messages]);
    }
}
