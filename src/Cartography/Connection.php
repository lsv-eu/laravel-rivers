<?php

namespace LsvEu\Rivers\Cartography;

class Connection extends RiverElement
{
    public string $startId;

    public ?string $startConditionId;

    public string $endId;

    /**
     * @param array{
     *     id: ?string,
     *     startId: string,
     *     startConditionId: ?string,
     *     endId: string,
     * } $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->startId = $attributes['startId'];
        $this->startConditionId = $attributes['startConditionId'] ?? null ?: null;
        $this->endId = $attributes['endId'];
    }

    public static function startOptions(RiverMap $map): array
    {
        return array_merge(
            $map->bridges->pluck('id')->all(),
            $map->forks->pluck('id')->all(),
            $map->rapids->pluck('id')->all(),
            $map->launches->pluck('id')->all(),
        );
    }

    public static function startConditionOptions(RiverMap $map, string $forkId): array
    {
        return $map->forks->get($forkId)->conditions->pluck('id')->all();
    }

    public static function endOptions(RiverMap $map, bool $startIsFork = false): array
    {
        return array_merge(
            $map->bridges->pluck('id')->all(),
            $startIsFork ? [] : $map->forks->pluck('id')->all(),
            $map->rapids->pluck('id')->all(),
            $map->launches->pluck('id')->all(),
        );
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'startId' => $this->startId,
            'startConditionId' => $this->startConditionId,
            'endId' => $this->endId,
        ];
    }

    public function validate(RiverMap $map): ?array
    {
        $errors = [];

        $start = $map->getElementById($this->startId);
        $end = $map->getElementById($this->endId);

        if (! in_array($this->startId, $this->startOptions($map))) {
            $errors['startId'] = "Connection start, $this->startId, does not exist.";
        }

        if ($this->startConditionId !== null && ! in_array($this->startConditionId, $this->startConditionOptions($map, $this->startId))) {
            $errors['startConditionId'] = "Connection start condition, $this->startConditionId, does not exist on fork, $this->startId.";
        }

        if ($this->startId === $this->endId) {
            $errors['endId'] = 'Connection end cannot be same as start.';
        } elseif ($start instanceof Fork && $end instanceof Fork) {
            $errors['endId'] = 'Connection end cannot be a fork if the start is a fork.';
        } elseif (! in_array($this->endId, $this->endOptions($map, (bool) $this->startConditionId))) {
            $errors['endId'] = "Connection end, $this->endId, does not exist.";
        }

        return $errors ?: null;
    }

    public static function make(string $startId, ?string $startCondition, string $endId): Connection
    {
        return new Connection([
            'startId' => $startId,
            'startConditionId' => $startCondition,
            'endId' => $endId,
        ]);
    }
}
