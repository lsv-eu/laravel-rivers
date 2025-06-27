<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Models\RiverRun;

class GetRiverRunInjections
{
    public function __construct(protected RiverRun $run) {}

    public function handle(bool $withSweeps = true): array
    {
        return [
            ...when($withSweeps, $this->run->sweeps->map(fn ($sweep) => fn () => $sweep), []),
            ...collect($this->run->raft->getInjectionNames())
                ->mapWithKeys(fn ($name) => [$name => fn () => $this->run->raft->resolveProvidedInjection($name)])
                ->toArray(),
            'map' => fn () => $this->run->river->map,
            'raft' => fn () => $this->run->raft,
            'river' => fn () => $this->run->river,
            'riverRun' => fn () => $this->run,
            'run' => fn () => $this->run,
        ];
    }

    public static function run(RiverRun $run, bool $withSweeps = true): array
    {
        return (new static($run))->handle($withSweeps);
    }
}
