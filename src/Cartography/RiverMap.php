<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use LsvEu\Rivers\Cartography\Traits\SerializesData;
use LsvEu\Rivers\Contracts\Raft;

class RiverMap implements \JsonSerializable, Arrayable, CastsAttributes
{
    use SerializesData;

    /**
     * @var RiverElementCollection<string, Bridge>
     */
    public RiverElementCollection $bridges;

    /**
     * @var RiverElementCollection<string, Connection>
     */
    public RiverElementCollection $connections;

    /**
     * @var RiverElementCollection<string, Fork>
     */
    public RiverElementCollection $forks;

    /**
     * @var RiverElementCollection<string, Rapid>
     */
    public RiverElementCollection $rapids;

    public bool $repeatable;

    /**
     * @var RiverElementCollection<string, Source>
     */
    public RiverElementCollection $sources;

    public function __construct(array $attributes = [])
    {
        $this->bridges = RiverElementCollection::make($attributes['bridges'] ?? [], Bridge::class);
        $this->connections = RiverElementCollection::make($attributes['connections'] ?? [], Connection::class);
        $this->forks = RiverElementCollection::make($attributes['forks'] ?? [], Fork::class);
        $this->rapids = RiverElementCollection::make($attributes['rapids'] ?? [], Rapid::class);
        $this->sources = RiverElementCollection::make($attributes['sources'] ?? [], Source::class);

        $this->repeatable = false;
    }

    public function getElementById(string $id): ?RiverElement
    {
        return $this->getAllRiverElements()->get($id);
    }

    public function getAllRiverElements(): Collection
    {
        return collect([
            ...$this->bridges->getAllRiverElements(),
            ...$this->connections->getAllRiverElements(),
            ...$this->forks->getAllRiverElements(),
            ...$this->rapids->getAllRiverElements(),
            ...$this->sources->getAllRiverElements(),
        ])
            ->flatten(1)
            ->keyBy('id');
    }

    public function getInterruptListeners(Raft $raft): array
    {
        return $this->sources->map(fn (Source $source) => $source->getInterruptListener($raft))->filter()->all();
    }

    public function getSourceByStartListener(string $event): Source
    {
        return $this->sources
            ->mapWithKeys(fn (Source $source) => [$source->getStartListener() => $source])
            ->get($event);
    }

    public function getStartListeners(): array
    {
        return $this->sources->map(fn (Source $source) => $source->getStartListener())->filter()->all();
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }

    public function toArray(): array
    {
        return [
            'sources' => $this->sources->toArray(),
            'rapids' => $this->rapids->toArray(),
            'connections' => $this->connections->toArray(),
            'forks' => $this->forks->toArray(),
            'bridges' => $this->bridges->toArray(),
            'repeatable' => $this->repeatable,
        ];
    }

    public function validate(): array
    {
        // The collect() on each collection is necessary to convert it to a normal collection or else ->toArray() blows
        // up since it's expecting different content.
        return collect([
            'bridges' => $this->bridges
                ->collect()
                ->filter(fn ($bridge) => ! $bridge instanceof Bridge),
            'connections' => $this->connections
                ->collect()
                ->map(fn ($connection, $id) => when(
                    $connection instanceof Connection,
                    $connection->validate($this),
                    'Connection must be a \LsvEu\Rivers\Cartography\Connection object',
                ))
                ->filter(),
            'forks' => $this->forks
                ->collect()
                ->map(fn ($fork, $id) => when(
                    $fork instanceof Fork,
                    $fork->validate($this),
                    'Fork must be a \LsvEu\Rivers\Cartography\Fork object',
                ))
                ->filter(),
            'rapids' => $this->rapids
                ->collect()
                ->map(fn ($rapid, $id) => when(
                    $rapid instanceof Rapid,
                    $rapid->validate($this),
                    'Rapid must be a \LsvEu\Rivers\Cartography\Rapid object',
                ))
                ->filter(),
            'sources' => $this->sources
                ->collect()
                ->map(fn ($source, $id) => when(
                    $source instanceof Source,
                    $source->validate($this),
                    "Source must be a \LsvEu\Rivers\Cartography\Source object",
                ))
                ->filter(),
        ])
            ->filter(fn (Collection $set) => $set->isNotEmpty())
            ->toArray();
    }
}
