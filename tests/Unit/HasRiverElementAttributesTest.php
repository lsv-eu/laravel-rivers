<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\RiverElement;


it('should have a trait property', function () {
    trait Labelable
    {
        public string $label;

        public function hydrateLabelable(array $attributes): void
        {
            $this->label = $attributes['label'] ?? '';
        }

        public function toArrayLabelable(): array
        {
            return ['label' => $this->label,];
        }
    }

    $test = new class(['label' => 'a label']) extends RiverElement {
        use Labelable;
    };

    expect($test->toArray())->toHaveKey('label', 'a label');
});