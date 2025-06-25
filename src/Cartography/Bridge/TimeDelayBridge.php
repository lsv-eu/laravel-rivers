<?php

namespace LsvEu\Rivers\Cartography\Bridge;

use DateInterval;
use Exception;
use LsvEu\Rivers\Cartography\Bridge;
use LsvEu\Rivers\Models\RiverRun;
use LsvEu\Rivers\Models\RiverTimedBridge;

class TimeDelayBridge extends Bridge
{
    public string $duration;

    /**
     * @throws Exception
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->duration = $attributes['duration'] ?? 'PT1D';

        // Throw error if invalid ISO 8601 duration
        new DateInterval($this->duration);
    }

    public function process(?RiverRun $riverRun = null): void
    {
        RiverTimedBridge::create([
            'river_run_id' => $riverRun->id,
            'resume_at' => now()->add(new DateInterval($this->duration)),
            'location' => $this->id,
            'paused' => ! $riverRun->running,
        ]);

        $riverRun->at_bridge = true;
        $riverRun->save();
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'duration' => $this->duration,
        ];
    }
}
