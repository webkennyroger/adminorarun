<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;

    public $is_liked;

    public $likes_count;

    public $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($id, $isLiked, $likesCount, $userId)
    {
        $this->id = $id;
        $this->is_liked = $isLiked;
        $this->likes_count = $likesCount;
        $this->user_id = (string) $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('timeline'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'like.toggled';
    }
}
