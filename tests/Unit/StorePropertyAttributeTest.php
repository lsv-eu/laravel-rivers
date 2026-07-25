<?php

namespace Tests\Unit;

use LsvEu\Rivers\Attributes\StoreProperty;
use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Exceptions\PropertyNotDefinedException;
use Tests\Unit\Classes\HasTestAttribute;

it('should set the property to the default value if not provided', function () {
    $test = new class extends RiverElement
    {
        #[StoreProperty]
        public ?bool $test1;

        #[StoreProperty(default: true)]
        public bool $test2;
    };

    expect($test)
        ->toHaveProperty('test1', null)
        ->toHaveProperty('test2', true);
});

it('should throw and error if the property is not specified when setDefault is false', function () {
    new class extends RiverElement
    {
        #[StoreProperty(setDefault: false)]
        public bool $test;
    };
})->throws(PropertyNotDefinedException::class, 'Property "test" is not defined');

it('should have the property set when provided', function () {
    $test = new class(['test' => true]) extends RiverElement
    {
        #[StoreProperty(setDefault: false)]
        public bool $test;
    };

    expect($test)->toHaveProperty('test', true);
});

it('should add the property to the toArray method', function () {
    $test = new class(['test' => true]) extends RiverElement
    {
        #[StoreProperty]
        public bool $test;
    };

    expect($test->toArray())->toHaveKey('test', true);
});

it('should add the property to the toArray method if defined in trait', function () {
    $test = new class(['test' => true]) extends RiverElement
    {
        use HasTestAttribute;
    };

    expect($test->toArray())->toHaveKey('test', true);
});

it('should work if added to a parent class', function () {
    class SPATTestClass extends RiverElement
    {
        use HasTestAttribute;
    }

    $test = new class(['test' => true]) extends SPATTestClass {};

    expect($test->toArray())->toHaveKey('test', true);
});
