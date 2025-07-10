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
    $badObject = new class extends RiverElement
    {
        public string $id = 'foo';
    };

    $connectionMap = new RiverMap;
    expect($connectionMap->validate())->not()->toHaveKey('connections');
    $connectionMap->connections->put('foo', $badObject);
    expect($errors = $connectionMap->validate())->toHaveKey('connections')
        ->and($errors['connections'])->toHaveKey('foo')
        ->and($errors['connections']['foo'])->toBe('Connection must be a \LsvEu\Rivers\Cartography\Connection object');

    $forkMap = new RiverMap;
    expect($forkMap->validate())->not()->toHaveKey('forks');
    $forkMap->forks->put('foo', $badObject);
    expect($errors = $forkMap->validate())->toHaveKey('forks')
        ->and($errors['forks'])->toHaveKey('foo')
        ->and($errors['forks']['foo'])->toBe('Fork must be a \LsvEu\Rivers\Cartography\Fork object');

    $rapidMap = new RiverMap;
    expect($rapidMap->validate())->not()->toHaveKey('rapids');
    $rapidMap->rapids->put('foo', $badObject);
    expect($errors = $rapidMap->validate())->toHaveKey('rapids')
        ->and($errors['rapids'])->toHaveKey('foo')
        ->and($errors['rapids']['foo'])->toBe('Rapid must be a \LsvEu\Rivers\Cartography\Rapid object');

    $sourceMap = new RiverMap;
    expect($sourceMap->validate())->not()->toHaveKey('sources');
    $sourceMap->sources->put('foo', $badObject);
    expect($errors = $sourceMap->validate())->toHaveKey('sources')
        ->and($errors['sources'])->toHaveKey('foo')
        ->and($errors['sources']['foo'])->toBe('Source must be a \LsvEu\Rivers\Cartography\Source object');
});

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
