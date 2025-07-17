<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use LsvEu\Rivers\Cartography\Traits\SerializesData;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

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

    public ?string $raftClass;

    public function __construct(array $attributes = [])
    {
        $this->bridges = RiverElementCollection::make($attributes['bridges'] ?? [], Bridge::class);
        $this->connections = RiverElementCollection::make($attributes['connections'] ?? [], Connection::class);
        $this->forks = RiverElementCollection::make($attributes['forks'] ?? [], Fork::class);
        $this->rapids = RiverElementCollection::make($attributes['rapids'] ?? [], Rapid::class);
        $this->launches = RiverElementCollection::make($attributes['launches'] ?? [], Launch::class);

        $this->raftClass = $attributes['raftClass'] ?? null;
        $this->repeatable = $attributes['repeatable'] ?? false;
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

    public function getInterruptListenerEvents(): array
    {
        return $this->launches
            ->map(fn (Launch $launch) => $launch->getInterruptListener())
            ->filter()
            ->unique()
            ->values()
            ->collect()
            ->toArray();
    }

    public function getLaunchByInterruptListener(RiverRun $river, string $event, array $additionalData): ?Launch
    {
        return $this->launches
            ->filter(fn (Launch $launch) => $launch->getInterruptListener() == $event)
            ->first(fn (Launch $launch) => $launch->check($river, $additionalData));
    }

    public function getLaunchByStartListener(River|RiverRun $river, string $event, array $additionalData = []): ?Launch
    {
        return $this->launches
            ->filter(fn (Launch $launch) => $launch->getStartListener() == $event)
            ->first(fn (Launch $launch) => $launch->check($river, $additionalData));
    }

    public function getListenerEvents(): array
    {
        return array_merge($this->getStartListenerEvents(), $this->getInterruptListenerEvents());
    }

    public function getStartListenerEvents(): array
    {
        return $this->launches
            ->map(fn (Launch $launch) => $launch->getStartListener())
            ->filter()
            ->unique()
            ->values()
            ->collect()
            ->toArray();
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
            'raftClass' => $this->raftClass,
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
            ->put('raftClass', $this->validateRaft())
            ->filter()
            ->toArray();
    }

    protected function validateRaft(): ?string
    {
        if ($this->raftClass === null) {
            return 'A raft class must be provided.';
        }
        if (! class_exists($this->raftClass)) {
            return "The raft class {$this->raftClass} does not exist.";
        }
        if (! is_subclass_of($this->raftClass, Raft::class)) {
            return "The raft {$this->raftClass} must be a subclass of \LsvEu\Rivers\Contracts\Raft.";
        }

        return null;
    }
}
