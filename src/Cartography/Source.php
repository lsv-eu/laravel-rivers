<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Models\RiverRun;

abstract class Source extends RiverElement
{
    public string $id;

    /**
     * @var RiverElementCollection<string, Condition>
     */
    public RiverElementCollection $conditions;

    public bool $enabled;

    public bool $restartable = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->conditions = RiverElementCollection::make($attributes['conditions'] ?? []);

        $this->enabled = $attributes['enabled'] ?? false;

        $this->restartable = $attributes['restartable'] ?? false;
    }

    public function check(RiverRun $run): bool
    {
        return $this->conditions->reduce(
            callback: fn (bool $carry, Condition $condition) => $carry && EvaluateRiverElement::run($run, $condition),
            initial: true,
        );
    }

    public function createRaft(): mixed
    {
        return [];
    }

    public function getInterruptListener(Raft $raft): ?string
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
}
