<?php

namespace LsvEu\Rivers\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LsvEu\Rivers\Models\River;

class RiverPausedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public River $river) {}
}
