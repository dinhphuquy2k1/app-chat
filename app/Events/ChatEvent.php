<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Conversation;
class ChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation_id;
    public $message;

    public $user;

    public $message_type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($conversation_id, $message, $user, $message_type)
    {
        $this->conversation_id = $conversation_id;
        $this->message = $message;
        $this->user = $user;
        $this->message_type = $message_type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ["group-$this->conversation_id"];
    }

    public function broadcastAs()
    {
        return 'chat-event';
    }
}
