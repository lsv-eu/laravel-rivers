<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Unit\Classes\TestRaft;

it('should store rafts', function () {
    $river = River::create([
        'title' => 'Test',
        'map' => new RiverMap,
    ]);

    RiverRun::create([
        'river_id' => $river->id,
        'raft' => new TestRaft(['name' => 'John Smith']),
    ]);

    expect($river->riverRuns()->count())->toBe(1)
        ->and($river->riverRuns->first()->raft->name)->toBe('John Smith');
});

it('should store sweeps', function () {
    $river = River::create([
        'title' => 'Test',
        'map' => new RiverMap,
    ]);

    RiverRun::create([
        'river_id' => $river->id,
        'raft' => new TestRaft(['name' => 'John Smith']),
        'sweeps' => [
            'father' => new TestRaft(['name' => 'Joe Smith']),
            'mother' => new TestRaft(['name' => 'Mary Smith']),
        ],
    ]);

    expect($river->riverRuns()->count())->toBe(1)
        ->and($river->riverRuns->first()->sweeps->get('father')->name)->toBe('Joe Smith')
        ->and($river->riverRuns->first()->sweeps->get('mother')->name)->toBe('Mary Smith');

});
