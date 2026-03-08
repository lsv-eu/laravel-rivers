<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverVersion;
use Tests\Feature\Classes\TestRaftMap;

it('should create version on creation', function () {
    $map = new TestRaftMap;
    $river = River::create([
        'title' => 'test',
        'map' => $map,
    ]);

    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeNull();
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->map)->toBeNull();
});

it('should not update current map when draft', function () {
    $map = new TestRaftMap;
    $river = River::create([
        'title' => 'test',
        'map' => $map,
    ]);

    expect($river->map)->toBeNull();

    $map2 = $river->workingVersion->map;
    $map2->forks->push(new Fork);
    $river->map = $map2;
    $river->save();

    expect($river->versions()->count())->toBe(1);
    expect($river->current_version_id)->toBeNull();
    expect($river->currentVersion)->toBeNull();
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->current_version_id)->not->toBe($river->working_version_id);
    expect($river->map)->toBeNull();
    expect($river->workingVersion->map->toArray())->toBe($map2->toArray());
});

it('should create and update new version when active', function () {
    $map = new TestRaftMap;
    $river = River::create([
        'title' => 'test',
        'map' => $map,
        'status' => 'active',
    ]);

    expect($river->versions()->count())->toBe(1);
    expect($river->currentVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->workingVersion)->toBeInstanceOf(RiverVersion::class);
    expect($river->map->toArray())->toBe($map->toArray());

    $map2 = $river->map;
    $map2->forks->push(new Fork);
    $river->map = $map2;
    $river->save();

    expect($river->versions()->count())->toBe(2);
    expect($river->current_version_id)->not()->toBe($river->working_version_id);
    expect($river->map->toArray())->toBe($map->toArray());
    expect($river->workingVersion->map->toArray())->toBe($map2->toArray());
});
