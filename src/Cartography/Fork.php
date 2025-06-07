<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Cartography\Fork\Condition;
use LsvEu\Rivers\Contracts\Raft;

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

    public function toArray(): array
    {
        return parent::toArray() + [
            'conditions' => $this->conditions->toArray(),
        ];
    }

    /**
     * Determines and returns the next identifier based on the specified raft and conditions.
     *
     * @param  Raft  $raft  The raft object to be evaluated by the conditions.
     * @return string The identifier of the next item, or the fork's identifier if no condition is satisfied.
     */
    public function getNext(Raft $raft): string
    {
        return $this->conditions->first(fn (Condition $condition) => $condition->check($raft))?->id ?? $this->id;
    }
}
