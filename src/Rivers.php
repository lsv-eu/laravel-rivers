<?php

namespace LsvEu\Rivers;

use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Models\HasObservers;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

class Rivers
{
    use HasObservers;

    public function __construct()
    {
        $this->loadObservers();
    }

    public function trigger(string $event, CreatesRaft $model, bool $eventHasId = false): void
    {
        if ($eventHasId) {
            RiverRun::query()
                ->hasListener($event)
                ->chunk(100, function ($runs) use ($model, $event) {
                    foreach ($runs as $run) {
                        $run->riverInterrupts()->create([
                            'event' => $event,
                            'details' => $model->createRaft(),
                        ]);
                    }
                });
        }

        $startEvent = $eventHasId ? str($event)->explode('.')->slice(0, -1)->implode('.') : $event;
        River::query()
            ->hasListener($startEvent)
            ->active()
            ->chunk(100, function ($rivers) use ($model, $startEvent, $eventHasId) {
                foreach ($rivers as $river) {
                    // TDOD: Check if this needs re-thought because of source conditions
                    if ($eventHasId) {
                        $latestRun = $river->riverRuns()->latest()->first();
                        // Don't start a new run if:
                        //  - there is a current river-run
                        //  - the river is not repeatable and has been run
                        if ($latestRun?->location || ($latestRun && ! $river->map->repeatable)) {
                            continue;
                        }
                    }
                    $source = $river->map->getSourceByStartListener($startEvent);
                    if ($source->check($model)) {
                        $river->startRun($startEvent, $model, $source);
                    }
                }
            });
    }
}
