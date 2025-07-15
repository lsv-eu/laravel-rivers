<?php

namespace Tests\Feature;

use Illuminate\Events\CallQueuedListener;
use LsvEu\Rivers\Cartography\Bridges\TimeDelayBridge;
use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Listeners\PauseRiverTimedBridges;
use LsvEu\Rivers\Listeners\ResumeRiverTimedBridges;
use LsvEu\Rivers\Models\River;
use Queue;
use Tests\Feature\Classes\BasicUserMap;
use Tests\Feature\Classes\PausingRipple;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Launches\UserCreated;

it('should not queue listeners when disabled', function () {
    config()->set('rivers.use_timed_bridges', false);
    Queue::fake();

    $river = River::create(['title' => 'Test River', 'map' => new BasicUserMap]);
    $river->pause();
    $river->resume();

    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === PauseRiverTimedBridges::class);
    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === ResumeRiverTimedBridges::class);
});

it('should queue listeners when enabled and handle pausing', function () {
    config()->set('rivers.use_timed_bridges', true);
    Queue::fake();

    $river = River::create(['title' => 'Test River', 'map' => new BasicUserMap, 'status' => 'active']);
    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === PauseRiverTimedBridges::class);
    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === ResumeRiverTimedBridges::class);

    $river->pause();
    Queue::assertPushed(CallQueuedListener::class, fn ($listener) => $listener->class === PauseRiverTimedBridges::class);
    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === ResumeRiverTimedBridges::class);

    Queue::fake(); // Reset queue

    $river->resume();
    Queue::assertNotPushed(CallQueuedListener::class, fn ($listener) => $listener->class === PauseRiverTimedBridges::class);
    Queue::assertPushed(CallQueuedListener::class, fn ($listener) => $listener->class === ResumeRiverTimedBridges::class);
});

it('should resume processing', function () {
    $map = new BasicUserMap([
        'launches' => [new UserCreated(['id' => 'user-created'])],
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

    $this->travelTo('2020-01-01 01:00:02');
    User::factory()->create();

    expect($river->riverRuns->count())->toBe(1)
        ->and($river->riverRuns->first()->status)->toBe('bridge')
        ->and($river->riverRuns->first()->location)->toBe('time-bridge')
        ->and($river->riverRuns->first()->riverTimedBridge->resume_at->format('Y-m-d H:i:s'))->toBe('2020-01-02 01:00:00');
    // ->and($river->riverRuns->first());

    $this->travelTo('2020-01-02 00:59:30');
    $this->artisan('rivers:check_timed_bridges', ['--exact' => true]);
    $river->refresh();
    expect($river->riverRuns->first()->status)->toBe('bridge');

    $this->travelTo('2020-01-02 01:00:30');
    $this->artisan('rivers:check_timed_bridges', ['--exact' => true]);
    $river->refresh();
    expect($river->riverRuns->first()->status)->toBe('paused')
        ->and($river->riverRuns->first()->location)->toBe('pause-rapid')
        ->and($river->riverRuns->first()->riverTimedBridge)->toBeNull();
});
