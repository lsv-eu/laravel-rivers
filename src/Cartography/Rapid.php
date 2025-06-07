<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Contracts\Raft;

class Rapid extends RiverElement
{
    public string $label = '';

    /**
     * @var RiverElementCollection<string, Ripple>
     */
    public RiverElementCollection $ripples;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->label = $attributes['label'] ?? $this->label ?: $this->label;
        $this->ripples = RiverElementCollection::make($attributes['ripples'] ?? []);
    }

    public function process(Raft $raft): void
    {
        $this->ripples
            ->each(function (Ripple $ripple) use ($raft) {
                $ripple->process($raft);
            });
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'label' => $this->label,
            'ripples' => $this->ripples->toArray(),
        ];
    }
}
