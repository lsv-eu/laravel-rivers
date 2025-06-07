<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Cartography\Source;

it('is valid with empty collections', function () {
    $map = new RiverMap;

    expect($map->isValid())->toBeTrue();
});

it('is valid with valid collections', function () {
    $fork = new class extends Fork {};
    $rapid = new class extends Rapid {};
    $source = new class extends Source {};

    $map = new RiverMap([
        'connections' => [new Connection(['startId' => 'foo', 'endId' => 'bar'])],
        'forks' => [new $fork(['id' => 'foo'])],
        'rapids' => [new $rapid(['id' => 'bar'])],
        'sources' => [new $source],
    ]);

    expect($map->isValid())->toBeTrue();
});

it('is invalid with invalid collections', function () {
    $badObject = new class extends RiverElement {};

    $badConnectionMap = new RiverMap;
    expect($badConnectionMap->isValid())->toBeTrue();
    $badConnectionMap->connections->put('foo', $badObject);
    expect($badConnectionMap->isValid())->toBeFalse();

    $badForkMap = new RiverMap;
    expect($badForkMap->isValid())->toBeTrue();
    $badForkMap->forks->put('foo', $badObject);
    expect($badForkMap->isValid())->toBeFalse();

    $badRapidMap = new RiverMap;
    expect($badRapidMap->isValid())->toBeTrue();
    $badRapidMap->rapids->put('foo', $badObject);
    expect($badRapidMap->isValid())->toBeFalse();

    $badSourceMap = new RiverMap;
    expect($badSourceMap->isValid())->toBeTrue();
    $badSourceMap->sources->put('foo', $badObject);
    expect($badSourceMap->isValid())->toBeFalse();
});
