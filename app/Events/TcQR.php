<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TcQR implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $code;
    public ?string $user_name;
    public ?string $entry_time;
    public ?string $exit_time = null;

    public function __construct(string $code, ?string $user_name, ?string $entry_time, ?string $exit_time)
    {
        $this->code = $code;
        $this->user_name = $user_name;
        $this->entry_time = $entry_time;
        $this->exit_time = $exit_time;
    }

    public function broadcastOn()
    {
        return ['EasyAccess'];
    }

    public function broadcastAs()
    {
        return 'code-channel';
    }
}
