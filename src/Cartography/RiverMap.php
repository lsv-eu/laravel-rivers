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
     * @var RiverElementCollection<string, Launch>
     */
    public RiverElementCollection $launches;

    public function __construct(array $attributes = [])
    {
        $this->bridges = RiverElementCollection::make($attributes['bridges'] ?? [], Bridge::class);
        $this->connections = RiverElementCollection::make($attributes['connections'] ?? [], Connection::class);
        $this->forks = RiverElementCollection::make($attributes['forks'] ?? [], Fork::class);
        $this->rapids = RiverElementCollection::make($attributes['rapids'] ?? [], Rapid::class);
        $this->launches = RiverElementCollection::make($attributes['launches'] ?? [], Launch::class);

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
            ...$this->launches->getAllRiverElements(),
        ])
            ->flatten(1)
            ->keyBy('id');
    }

    public function getInterruptListenerEvents(Raft $raft): array
    {
        return $this->getInterruptListeners($raft)->keys()->all();
    }

    /**
     * @return Collection<string, Launch>
     */
    public function getInterruptListeners(Raft $raft): Collection
    {
        return $this->launches
            ->mapWithKeys(fn (Launch $launch) => [$launch->getInterruptListener($raft) => $launch])
            ->filter();
    }

    public function getLaunchByInterruptListener(string $event, Raft $raft): ?Launch
    {
        return $this->getInterruptListeners($raft)->get($event);
    }

    public function getLaunchByStartListener(string $event): ?Launch
    {
        return $this->getStartListeners()->get($event);
    }

    public function getStartListenerEvents(): array
    {
        return $this->getStartListeners()->keys()->all();
    }

    /**
     * @return Collection<string, Launch>
     */
    public function getStartListeners(): Collection
    {
        return $this->launches
            ->mapWithKeys(fn (Launch $launch) => [$launch->getStartListener() => $launch])
            ->filter();
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }

    public function toArray(): array
    {
        return [
            'launches' => $this->launches->toArray(),
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
            'launches' => $this->launches
                ->collect()
                ->map(fn ($launch, $id) => when(
                    $launch instanceof Launch,
                    $launch->validate($this),
                    "Launch must be a \LsvEu\Rivers\Cartography\Launch object",
                ))
                ->filter(),
        ])
            ->filter(fn (Collection $set) => $set->isNotEmpty())
            ->toArray();
    }
}
