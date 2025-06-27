<?php

namespace LsvEu\Rivers\Cartography\Bridge;

use DateInterval;
use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Cartography\Bridge;
use LsvEu\Rivers\Models\RiverRun;
use LsvEu\Rivers\Models\RiverTimedBridge;

abstract class TimedBridge extends Bridge
{
    abstract public function getDateInterval(): DateInterval;

    public function process(?RiverRun $riverRun = null): void
    {
        RiverTimedBridge::create([
            'river_run_id' => $riverRun->id,
            'resume_at' => now()->add(EvaluateRiverElement::run($riverRun, $this, 'getDateInterval')),
            'location' => $this->id,
            'paused' => $riverRun->status === 'paused',
        ]);

        $riverRun->status = 'bridge';
        $riverRun->save();
    }
}
