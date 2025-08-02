<?php

namespace LsvEu\Rivers\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LsvEu\Rivers\Models\River;

class ResumeRiverTimedBridges implements ShouldQueue
{
    public function handle(River $river): void
    {
        // Resume timed bridges that should have run while paused
        $river->riverTimedBridges()
            ->wherePaused(true)
            ->where('resume_at', 'lte', now()->addMinute())
            ->update([
                'paused' => false,
                'resume_at' => now()->addMinutes(2),
            ]);

        // Resume the rest of the timed bridges
        $river->riverTimedBridges()
            ->wherePaused(true)
            ->update(['paused' => false]);
    }

    public function shouldQueue(): bool
    {
        return (bool) config('rivers.use_timed_bridges');
    }
}
