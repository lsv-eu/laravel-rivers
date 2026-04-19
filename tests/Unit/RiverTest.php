<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Exceptions\InvalidRiverMapException;
use LsvEu\Rivers\Exceptions\InvalidRiverStatusException;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverVersion;
use Tests\Feature\Classes\TestRaftMap;

it('should accept a valid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new TestRaftMap,
    ]);
})->throwsNoExceptions();

it('should throw an exception for an invalid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new TestRaftMap([
            'connections' => [
                Connection::make('start', null, 'end'),
            ],
        ]),
    ]);
})->throws(InvalidRiverMapException::class);

it('should throw an exception when assigning a draft status to active or paused', function () {
    $river = River::create([
        'title' => 'test',
        'status' => 'paused',
        'map' => new TestRaftMap,
    ]);
    $river->update(['status' => 'draft']);
})->throws(InvalidRiverStatusException::class);

it('should not throw an exception when assigning draft status to a new river', function () {
    River::create([
        'title' => 'test',
        'status' => 'draft',
        'map' => new TestRaftMap,
    ]);
    $river = River::create([
        'title' => 'test2',
        'status' => 'draft',
        'map' => new TestRaftMap,
    ]);
})->throwsNoExceptions();

it('should return a new map copy when accessed', function () {
    $river = River::create([
        'title' => 'test',
        'status' => 'paused',
        'map' => new TestRaftMap,
    ]);
    $map1 = $river->map;
    $map2 = $river->map;
    expect($map1)->not->toBe($map2); // not some object
    expect($map1->toArray())->toBe($map2->toArray()); // same content
});

it('should match versions when publishing from draft', function () {
    $map = new TestRaftMap;
    $river = River::create([
        'title' => 'test',
        'map' => $map,
    ]);

    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeNull();
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->map)->toBeNull();
    expect($river->workingVersion->published_at)->toBeNull();

    $river->publish();

    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->current_version_id)->toBe($river->working_version_id);
    expect($river->map->toArray())->toBe($map->toArray());
    expect($river->workingVersion->published_at)->not->toBeNull();
});

it('should create new version when active', function () {
    $map = new TestRaftMap;
    /** @var River $river */
    $river = River::create([
        'title' => 'test',
        'map' => $map,
        'status' => 'active',
    ]);

    expect($river->status)->toBe('active');
    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->currentVersion->published_at)->not->toBeNull();

    $map2 = $river->map;
    $map2->forks->push(new Fork);
    $river->map = $map2;
    $river->save();
    $river->refresh();

    expect($river->versions()->count())->toBe(2);
    expect($river->current_version_id)->not->toBe($river->working_version_id);
    expect($river->currentVersion->map->toArray())->not->toBe($river->workingVersion->map->toArray());
    expect($river->map->toArray())->toBe($map->toArray());
    expect($river->workingVersion->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->published_at)->not->toBeNull();
    expect($river->workingVersion->published_at)->toBeNull();

    $river->publish();

    expect($river->versions()->count())->toBe(2);
    expect($river->current_version_id)->toBe($river->working_version_id);
    expect($river->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->published_at)->not->toBeNull();
    expect($river->workingVersion->published_at)->not->toBeNull();
});

it('should create new version when paused', function () {
    $map = new TestRaftMap;
    $river = River::create([
        'title' => 'test',
        'map' => $map,
        'status' => 'paused',
    ]);

    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->currentVersion->published_at)->not->toBeNull();

    $map2 = $river->map;
    $map2->forks->push(new Fork);
    $river->map = $map2;
    $river->save();

    expect($river->versions()->count())->toBe(2);
    expect($river->current_version_id)->not->toBe($river->working_version_id);
    expect($river->map->toArray())->toBe($map->toArray());
    expect($river->workingVersion->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->published_at)->not->toBeNull();
    expect($river->workingVersion->published_at)->toBeNull();

    $river->publish();

    expect($river->versions()->count())->toBe(2);
    expect($river->current_version_id)->toBe($river->working_version_id);
    expect($river->map->toArray())->toBe($map2->toArray());
    expect($river->workingVersion->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->map->toArray())->toBe($map2->toArray());
    expect($river->currentVersion->published_at)->not->toBeNull();
    expect($river->workingVersion->published_at)->not->toBeNull();
});
