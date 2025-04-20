<?php

namespace Tests\Unit;

use Tests\Unit\Classes\TestRaft;

test('can get a property by name', function () {
    expect((new TestRaft(['name' => 'John']))->getProperty('name'))->toEqual('John');
});

test('can get a property by name magically', function () {
    expect((new TestRaft(['name' => 'John']))->name)->toEqual('John');
});

test('can get a dynamic property by name', function () {
    expect((new TestRaft(['name' => 'John']))->getProperty('upper_name'))->toEqual('JOHN');
});

test('can get a dynamic property by name magically', function () {
    expect((new TestRaft(['name' => 'John']))->upper_name)->toEqual('JOHN');
});
