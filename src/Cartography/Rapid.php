<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Actions\ProcessRiverElement;
use LsvEu\Rivers\Contracts\CanBeProcessed;
use LsvEu\Rivers\Models\RiverRun;

class Rapid extends RiverElement implements CanBeProcessed
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

    public function getAllRiverElements(): array
    {
        return array_merge([$this], $this->ripples->all());
    }

    public function process(?RiverRun $riverRun = null): void
    {
        $this->ripples
            ->each(fn (Ripple $ripple) => ProcessRiverElement::run($riverRun, $ripple->id));
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'label' => $this->label,
            'ripples' => $this->ripples->toArray(),
        ];
    }
}
