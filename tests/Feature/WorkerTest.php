<?php

use Illuminate\Support\Facades\Queue;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Cartography\Source\ModelCreated;
use LsvEu\Rivers\Jobs\ProcessRiverRun;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Traits\UsesConfig;
use Workbench\App\Models\Tag;
use Workbench\App\Models\Taggable;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Sources\TaggableCondition;

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

test('job should use the configured queue - too complex', function () {
    $tag1 = Tag::create(['name' => 'Test Default',  'type' => 'user']);

    River::create([
        'title' => 'Queue Test',
        'status' => 'active',
        'map' => new RiverMap([
            'sources' => [
                new ModelCreated([
                    'class' => Taggable::class,
                    'conditions' => [
                        new TaggableCondition([
                            'tagId' => $tag1->id,
                        ]),
                    ],
                ]),
            ],
        ]),
    ]);

    $user = User::factory()->createOne(['name' => 'John']);

    // Test the default
    Queue::fake();
    Queue::assertCount(0);
    $user->tags()->attach($tag1);
    Queue::assertPushedOn('default', ProcessRiverRun::class);
    Queue::assertCount(1);

    $user->tags()->detach($tag1);
    RiverRun::truncate();

    // Test with a different queue
    config()->set('rivers.queue', 'rivers');
    Queue::fake();
    Queue::assertCount(0);
    $user->tags()->attach($tag1);
    Queue::assertPushedOn('rivers', ProcessRiverRun::class);
    Queue::assertCount(1);
})->skip();

function createUserListeningRiver(string $status = 'active'): River
{
    return River::create([
        'title' => 'Queue Test',
        'status' => $status,
        'map' => new RiverMap([
            'sources' => [
                new ModelCreated([
                    'id' => 'user1',
                    'class' => User::class,
                ]),
            ],
        ]),
    ]);
}
