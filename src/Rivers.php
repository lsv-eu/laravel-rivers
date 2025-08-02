<?php

namespace LsvEu\Rivers;

use Illuminate\Support\Facades\Config;
use LsvEu\Rivers\Contracts\Raft;
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

    public function trigger(string $event, Raft $raft, array $eventData = []): void
    {
        River::query()
            ->hasListener($event)
            ->active()
            ->each(function (River $river) use ($eventData, $raft, $event) {
                if ($river->map->raftClass !== get_class($raft)) {
                    return;
                }

                if ($river->repeatable) {
                    $run = $river->riverRuns()
                        ->whereRaftId($raft->id)
                        ->whereNull('completed_at')
                        ->first();
                    if ($run === null) {
                        $this->launch($river, $event, $raft);
                    } else {
                        $this->createInterrupt($run, $event, $eventData);
                    }
                } else {
                    $run = $river->riverRuns()->whereRaftId($raft->id)->first();
                    if ($run === null) {
                        $this->launch($river, $event, $raft);
                    } elseif ($run->completed_at === null) {
                        $this->createInterrupt($run, $event, $eventData);
                    }
                }
            });
    }

    protected function createInterrupt(RiverRun $run, string $event, array $eventData = []): void
    {
        $launch = $run->river->map->getLaunchByInterruptListener($run, $event, ['eventData' => $eventData]);
        if ($launch) {
            $run->riverInterrupts()->create([
                'event' => $event,
                'details' => $eventData,
            ]);

            if ($run->status === 'bridge') {
                Config::get('rivers.job_class')::dispatch($run->id);
            }
        }
    }

    protected function launch(River $river, string $event, Raft $raft, array $eventData=[]): void
    {
        $launch = $river->map->getLaunchByStartListener($river, $event, ['raft' => $raft, 'eventData' => $eventData]);

        if ($launch) {
            $river->startRun($launch, $raft);
        }
    }
}
