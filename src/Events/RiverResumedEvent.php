<?php

namespace LsvEu\Rivers\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LsvEu\Rivers\Models\River;

class RiverResumedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public River $river) {}
}
