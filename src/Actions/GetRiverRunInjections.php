<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

class GetRiverRunInjections
{
    protected ?Raft $raft;

    protected River $river;

    protected ?RiverRun $run = null;

    public function __construct(River|RiverRun $river, ?Raft $raft = null)
    {
        if ($river instanceof RiverRun) {
            $this->river = $river->river;
            $this->run = $river;
            $this->raft = $river->raft;
        } else {
            $this->raft = $raft;
            $this->river = $river;
        }
    }

    public function handle(bool $withSweeps = true): array
    {
        return [
            ...when(
                condition: $this->run && $withSweeps,
                value: fn () => $this->run->sweeps->map(fn ($sweep) => fn () => $sweep),
                default: [],
            ),
            ...collect($this->raft->getInjectionNames())
                ->mapWithKeys(fn ($name) => [$name => fn () => $this->raft->resolveProvidedInjection($name)])
                ->toArray(),
            'map' => fn () => $this->river->map,
            'raft' => fn () => $this->raft,
            'river' => fn () => $this->river,
            'riverRun' => fn () => $this->run,
            'run' => fn () => $this->run,
        ];
    }

    public static function run(River|RiverRun $river, bool $withSweeps = true, ?Raft $raft = null): array
    {
        return (new static($river, $raft))->handle($withSweeps);
    }
}
