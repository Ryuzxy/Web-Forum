<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
    //  *
    //  * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PresenceChannel('channel.' . $this->message->channel_id);
    }
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'created_at' => $this->message->created_at->toDateTimeString(),
            'file' => $this->message->file_path ? [
                'path' => $this->message->file_path,
                'name' => $this->message->file_name,
                'mime' => $this->message->mime_type,
                'size' => $this->message->file_size,
            ] : null,
        ];
    }

}
