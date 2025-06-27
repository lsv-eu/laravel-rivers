<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Models\RiverRun;

class Fork extends RiverElement
{
    /**
     * @var RiverElementCollection<string, Condition>
     */
    public RiverElementCollection $conditions;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->conditions = RiverElementCollection::make($attributes['conditions'] ?? []);
    }

    public function getAllRiverElements(): array
    {
        return array_merge([$this], $this->conditions->all());
    }

    /**
     * Determines and returns the next identifier based on the specified raft and conditions.
     *
     * @return string The identifier of the next item, or the fork's identifier if no condition is satisfied.
     */
    public function getNext(RiverRun $run): string
    {
        return $this->conditions
            ->first(fn (Condition $condition) => EvaluateRiverElement::run($run, $condition))
            ?->id ?? $this->id;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'conditions' => $this->conditions->toArray(),
        ];
    }
}
