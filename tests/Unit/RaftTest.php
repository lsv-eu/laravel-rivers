<?php

namespace Tests\Unit;

use Tests\Unit\Classes\TestRaft;

test('can get property types', function () {
    expect((new TestRaft(['name' => 'test']))->getPropertyType())->toEqual(['name' => 'string', 'upper_name' => 'string']);
});

test('can get a property\'s type', function () {
    expect((new TestRaft(['name' => 'test']))->getPropertyType('name'))->toEqual('string');
});

test('bad dynamic property throws error', function () {
    expect(fn () => (new TestRaft(['name' => 'John']))->lower_name)
        ->toThrow(\Exception::class, 'Property does not exist. Tests\Unit\Classes\TestRaft does not have property, lower_name.');
});
