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

test('should not start if not active', function () {
    River::create([
        'title' => 'Queue Test',
        'status' => 'paused',
        'map' => new RiverMap([
            'sources' => [
                new ModelCreated([
                    'class' => User::class,
                ]),
            ],
        ]),
    ]);

    Queue::fake();
    Queue::assertCount(0);
    User::factory()->createOne(['name' => 'John']);
    Queue::assertCount(0);
});

test('should not start if active', function () {
    River::create([
        'title' => 'Queue Test',
        'status' => 'active',
        'map' => new RiverMap([
            'sources' => [
                new ModelCreated([
                    'class' => User::class,
                ]),
            ],
        ]),
    ]);

    Queue::fake();
    Queue::assertCount(0);
    User::factory()->createOne(['name' => 'John']);
    Queue::assertCount(1);
});

test('should use the configured queue', function () {
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
});
