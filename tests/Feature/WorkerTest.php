<?php

use Illuminate\Support\Facades\Queue;
use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Jobs\ProcessRiverRun;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Traits\UsesConfig;
use Workbench\App\Models\User;

uses(UsesConfig::class);

test('job should not start if not active', function () {
    createUserListeningRiver(status: 'paused');

    Queue::fake();
    Queue::assertCount(0);
    User::factory()->createOne(['name' => 'John']);
    Queue::assertCount(0);
});

test('job should start if active', function () {
    createUserListeningRiver();

    Queue::fake();
    Queue::assertCount(0);
    User::factory()->createOne(['name' => 'John']);
    Queue::assertCount(1);
});

test('job should use the configured queue', function () {
    createUserListeningRiver();

    // Test the default
    Queue::fake();
    Queue::assertCount(0);
    User::factory()->create();
    Queue::assertCount(1);
    Queue::assertPushedOn('default', ProcessRiverRun::class);

    // Test with a different queue
    config()->set('rivers.queue', 'rivers');
    Queue::fake();
    Queue::assertCount(0);
    User::factory()->create();
    Queue::assertCount(1);
    Queue::assertPushedOn('rivers', ProcessRiverRun::class);
});

test('job should complete without processing if paused', function () {
    $river = createUserListeningRiver(status: 'paused');

    $user = User::factory()->createOneQuietly();
    $riverRun = $river->riverRuns()->create([
        'location' => 'user1',
        'raft' => $user->createRaft(),
    ]);
    $job = (new ProcessRiverRun($riverRun->id))->withFakeQueueInteractions();
    $job->handle();
    $job->assertDeleted();
});

test('run should complete if no connections', function () {
    createUserListeningRiver();
    User::factory()->createOne();
    expect(RiverRun::first()->completed_at)->toBeObject();
});

function createUserListeningRiver(string $status = 'active'): River
{
    return River::create([
        'title' => 'Queue Test',
        'status' => $status,
        'map' => new RiverMap([
            'launches' => [
                new ModelCreated([
                    'id' => 'user1',
                    'class' => User::class,
                ]),
            ],
        ]),
    ]);
}
