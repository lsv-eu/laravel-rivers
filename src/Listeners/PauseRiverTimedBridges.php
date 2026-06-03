<?php

namespace LsvEu\Rivers\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LsvEu\Rivers\Models\River;

class PauseRiverTimedBridges implements ShouldQueue
{
    public function handle(River $river): void
    {
        $river->riverTimedBridges()->update(['paused' => true]);
    }

    public function shouldQueue(): bool
    {
        return (bool) config('rivers.timed_bridges.enabled');
    }
}
