<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Cartography\Launches;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Cartography\RiverMap;
use Workbench\App\Models\Article;
use Workbench\App\Rivers\Rafts\ArticleRaft;

it('is valid with empty collections', function () {
    $map = new RiverMap([
        'raftClass' => ArticleRaft::class,
    ]);

    expect($map->isValid())->toBeTrue();
});

it('is valid with valid collections', function () {
    $fork = new class extends Fork {};
    $rapid = new class extends Rapid {};
    $launch = new class extends Launch {};

    $map = new RiverMap([
        'raftClass' => ArticleRaft::class,
        'connections' => [new Connection(['startId' => 'foo', 'endId' => 'bar'])],
        'forks' => [new $fork(['id' => 'foo'])],
        'launches' => [new $launch(['raftClass' => ArticleRaft::class])],
        'rapids' => [new $rapid(['id' => 'bar'])],
    ]);

    expect($map->isValid())->toBeTrue();
});

it('should be invalid with collections containing incorrect types', function () {
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

    $launchMap = new RiverMap;
    expect($launchMap->validate())->not()->toHaveKey('launches');
    $launchMap->launches->put('foo', $badObject);
    expect($errors = $launchMap->validate())->toHaveKey('launches')
        ->and($errors['launches'])->toHaveKey('foo')
        ->and($errors['launches']['foo'])->toBe('Launch must be a \LsvEu\Rivers\Cartography\Launch object');
});

it('should be invalid if a launch is invalid', function () {
    $map = new RiverMap([
        'raftClass' => ArticleRaft::class,
        'launches' => [
            new Launches\ModelCreated([
                'id' => 'launch-1',
                'class' => Article::class,
            ]),
        ],
    ]);

    $errors = $map->validate();
    expect($map->isValid())->toBeFalse()
        ->and($errors)->toHaveKey('launches')
        ->and($errors['launches'])->toHaveKey('launch-1');
});

it('should be valid if all launches are valid', function () {
    $map = new RiverMap([
        'raftClass' => ArticleRaft::class,
        'launches' => [
            new Launches\ModelCreated(['class' => Article::class, 'raftClass' => ArticleRaft::class]),
            new Launches\ModelCreated(['class' => Article::class, 'raftClass' => ArticleRaft::class]),
        ],
    ]);

    expect($map->isValid())->toBeTrue();
});
