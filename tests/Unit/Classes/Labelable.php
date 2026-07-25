<?php

namespace Tests\Unit\Classes;

use LsvEu\Rivers\Cartography\Concerns\HasRiverElementAttributes;

trait Labelable
{
    use HasRiverElementAttributes;

    public string $label;

    public function hydrateLabelable(array $attributes): void
    {
        $this->label = $attributes['label'] ?? '';
    }

    public function toArrayLabelable(): array
    {
        return ['label' => $this->label];
    }
}
