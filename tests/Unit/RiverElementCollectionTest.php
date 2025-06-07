<?php

use Tests\Traits\UsesConfig;

uses(UsesConfig::class);

test('serialization fails with bad elements', function () {
    $collection = new \LsvEu\Rivers\Cartography\RiverElementCollection;
    $collection->put('foo', new \Tests\Unit\Classes\BadRiverElement);

    expect(fn () => $collection->toArray())->toThrow(\Exception::class);
});

test('serialization results match', function () {
    $collection = new \LsvEu\Rivers\Cartography\RiverElementCollection;
    $collection->put('foo', new \Tests\Unit\Classes\GoodRiverElement(['id' => 'foo']));

    expect($collection->toArray())
        ->toEqual([
            ['Tests\Unit\Classes\GoodRiverElement', ['id' => 'foo']],
        ]);
});

test('unserialization fails for non-existent classes', function () {
    expect(fn () => \LsvEu\Rivers\Cartography\RiverElementCollection::make([
        ['NonExistentClass', ['id' => 'foo']],
    ]))->toThrow(\Exception::class, 'Class NonExistentClass not found');
});

test('unserialization fails for classes not extending RiverElement', function () {
    expect(fn () => \LsvEu\Rivers\Cartography\RiverElementCollection::make([
        [\Tests\Unit\Classes\BadRiverElement::class, ['id' => 'foo']],
    ]))->toThrow(
        \Exception::class,
        'Class Tests\Unit\Classes\BadRiverElement must extend or be LsvEu\Rivers\Cartography\RiverElement',
    );
});

test('unserialization passes for good classes', function () {
    $collection = \LsvEu\Rivers\Cartography\RiverElementCollection::make([
        [\Tests\Unit\Classes\GoodRiverElement::class, ['id' => 'foo']],
    ]);

    expect($collection->get('foo')->id)->toBe('foo');
});
