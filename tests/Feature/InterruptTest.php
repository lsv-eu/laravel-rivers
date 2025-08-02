<?php

namespace Tests\Feature;

use LsvEu\Rivers\Cartography\Bridges\TimeDelayBridge;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Cartography\Launches\ModelUpdated;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use Tests\Feature\Classes\PausingRipple;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

it('should resume run if bridged', function () {
    $map = new RiverMap([
        'raftClass' => UserRaft::class,
        'launches' => [
            new ModelCreated(['id' => 'user-created', 'class' => User::class, 'raftClass' => UserRaft::class]),
            new ModelUpdated(['id' => 'user-updated', 'class' => User::class, 'raftClass' => UserRaft::class]),
        ],
        'bridges' => [new TimeDelayBridge(['id' => 'time-bridge', 'duration' => 'P1D'])],
        'rapids' => [new Rapid(['id' => 'pause-rapid', 'ripples' => [new PausingRipple]])],
        'connections' => [
            new Connection(['startId' => 'user-created', 'endId' => 'time-bridge']),
            new Connection(['startId' => 'time-bridge', 'endId' => 'pause-rapid']),
        ],
    ]);

    $river = River::create([
        'title' => 'Test River',
        'status' => 'active',
        'map' => $map,
    ]);

    $user = User::factory()->create();

    expect($river->riverRuns->count())->toBe(1)
        ->and($river->riverRuns->first()->status)->toBe('bridge')
        ->and($river->riverRuns->first()->location)->toBe('time-bridge');

    $user->name .= ' Changed';
    $user->save();

    $river->refresh();
    expect($river->riverRuns->count())->toBe(1)
        ->and($river->riverRuns->first()->status)->toBe('completed')
        ->and($river->riverRuns->first()->location)->toBeNull();
});

it('should have more tests', function () {

})->skip();