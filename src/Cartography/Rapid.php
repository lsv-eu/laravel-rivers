<?php

namespace LsvEu\Rivers\Cartography;

class Rapid extends RiverElement
{
    public string $label = '';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->label = $attributes['label'] ?? '';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'label' => $this->label,
        ];
    }
}
