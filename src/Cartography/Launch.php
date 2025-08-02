<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

abstract class Launch extends RiverElement
{
    public string $id;

    /**
     * @var RiverElementCollection<string, Condition>
     */
    public RiverElementCollection $conditions;

    public bool $enabled;

    public ?string $raftClass;

    public bool $restartable = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->conditions = RiverElementCollection::make($attributes['conditions'] ?? []);

        $this->enabled = $attributes['enabled'] ?? false;

        $this->raftClass = $attributes['raftClass'] ?? null;

        $this->restartable = $attributes['restartable'] ?? false;
    }

    public function check(River|RiverRun $river, array $additionalData = []): bool
    {
        // Create a reusable evaluator so we aren't rebuilding dependency inject for each check
        $evaluator = new EvaluateRiverElement($river, $additionalData);

        return $this->conditions->reduce(
            callback: fn (bool $carry, Condition $condition) => $carry && $evaluator->handle($condition),
            initial: true,
        );
    }

    public function createRaft(): mixed
    {
        return [];
    }

    public function getInterruptListener(): ?string
    {
        return null;
    }

    public function getStartListener(): ?string
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'conditions' => $this->conditions->toArray(),
            'enabled' => $this->enabled,
            'restartable' => $this->restartable,
        ];
    }

    public function validate(RiverMap $map): ?array
    {
        $errors = [];
        if ($this->raftClass === null) {
            $errors['raftClass'] = 'A raft class must be provided.';
        } elseif ($this->raftClass != $map->raftClass) {
            $errors['raftClass'] = 'The raft class provided does not match the map raft class.';
        }

        return empty($errors) ? null : $errors;
    }
}
