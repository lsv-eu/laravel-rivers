<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use LsvEu\Rivers\Contracts\Raft;

class RiverMap implements \JsonSerializable, Arrayable, CastsAttributes
{
    use \LsvEu\Rivers\Cartography\Traits\SerializesData;

    public RiverElementCollection $connections;

    public RiverElementCollection $forks;

    public RiverElementCollection $rapids;

    public bool $repeatable;

    public RiverElementCollection $sources;

    public function __construct(array $attributes = [])
    {
        $this->connections = RiverElementCollection::make($attributes['connections'] ?? []);
        $this->forks = RiverElementCollection::make($attributes['forks'] ?? []);
        $this->rapids = RiverElementCollection::make($attributes['rapids'] ?? []);
        $this->sources = RiverElementCollection::make($attributes['sources'] ?? [], Source::class);

        $this->repeatable = false;
    }

    public function getInterruptListeners(Raft $raft): array
    {
        return $this->sources->map(fn (Source $source) => $source->getInterruptListener($raft))->filter()->all();
    }

    public function getStartListeners(): array
    {
        return $this->sources->map(fn (Source $source) => $source->getStartListener())->filter()->all();
    }

    public function toArray(): array
    {
        return [
            'sources' => $this->sources->toArray(),
            'rapids' => $this->rapids->toArray(),
            'connections' => $this->connections->toArray(),
            'forks' => $this->forks->toArray(),
            'repeatable' => $this->repeatable,
        ];
    }
}
