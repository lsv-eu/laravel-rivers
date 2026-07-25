<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\RiverElement;
use Tests\Unit\Classes\Labelable;

it('should have a trait property', function () {
    $test = new class(['label' => 'a label']) extends RiverElement
    {
        use Labelable;
    };

    expect($test->toArray())->toHaveKey('label', 'a label');
});

it('should have a trait property from a parent', function () {
    class ParentClass extends RiverElement
    {
        use Labelable;
    }

    $test = new class(['label' => 'a label']) extends ParentClass {};

    expect($test->toArray())->toHaveKey('label', 'a label');
});
