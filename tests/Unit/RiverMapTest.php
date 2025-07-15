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
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\ArticleRaft;
use Workbench\App\Rivers\Rafts\UserRaft;

it('is valid with empty collections', function () {
    $map = new RiverMap;

    expect($map->isValid())->toBeTrue();
});

it('is valid with valid collections', function () {
    $fork = new class extends Fork {};
    $rapid = new class extends Rapid {};
    $launch = new class extends Launch {};

    $map = new RiverMap([
        'connections' => [new Connection(['startId' => 'foo', 'endId' => 'bar'])],
        'forks' => [new $fork(['id' => 'foo'])],
        'rapids' => [new $rapid(['id' => 'bar'])],
        'launches' => [new $launch],
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

    $launchMap = new RiverMap;
    expect($launchMap->validate())->not()->toHaveKey('launches');
    $launchMap->launches->put('foo', $badObject);
    expect($errors = $launchMap->validate())->toHaveKey('launches')
        ->and($errors['launches'])->toHaveKey('foo')
        ->and($errors['launches']['foo'])->toBe('Launch must be a \LsvEu\Rivers\Cartography\Launch object');
});

it('should be invalid if the launches do not match the raft type', function () {
    $map = new RiverMap([
        'raftName' => 'article',
        'raftType' => UserRaft::class,
        'launches' => [new Launches\ModelCreated(['id' => 'launch-1', 'class' => Article::class])],
    ]);

    $errors = $map->validate();
    expect($map->isValid())->toBeFalse()
        ->and($errors)->toContain('launches')
        ->and($errors['launches'])->toContain('Launch does not provide a compatible raft type.');
})->skip();

it('should be invalid if any launch does not match the raft type', function () {
    $map = new RiverMap([
        'raftName' => 'article',
        'raftType' => ArticleRaft::class,
        'launches' => [
            new Launches\ModelCreated(['id' => 'launch-1', 'class' => Article::class]),
            new Launches\ModelCreated(['id' => 'launch-2', 'class' => User::class]),
        ],
    ]);

    $errors = $map->validate();
    expect($map->isValid())->toBeFalse()
        ->and($errors)->toContain('launches')
        ->and($errors['launches'])->toContain('Launch does not provide a compatible raft type.');
})->skip();

it('should be valid if all launches provide the raft type', function () {
    $map = new RiverMap([
        'raftName' => 'article',
        'raftType' => ArticleRaft::class,
        'launches' => [new Launches\ModelCreated(['id' => 'launch-1', 'class' => Article::class])],
    ]);

    expect($map->isValid())->toBeTrue();
});
