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
class CreatedConversationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userIds;
    public $result;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userIds, $result)
    {
        $this->userIds = $userIds;
        $this->result = $result;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        foreach ($this->userIds as $user_id) {
            array_push($channels, 'users.' . $user_id);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'create-conversation-event';
    }
}
