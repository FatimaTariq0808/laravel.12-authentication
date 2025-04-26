<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserRequestedPassword
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    public $resetURL;
    /**
     * Create a new event instance.
     */
    public function __construct(User $user,$resetURL)
    {
        \Log::info('UserRequestedPassword event triggered');
        $this->user = $user;
        $this->resetURL = $resetURL;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
