<?php

namespace Tests\Unit;

use LsvEu\Rivers\Actions\GetRiverRunInjections;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Unit\Classes\TestRaft;
use Workbench\App\Models\Article;
use Workbench\App\Rivers\Rafts\ArticleRaft;

it('should provide map, river, run and riverRun', function () {
    $ids = createRun();

    $injections = GetRiverRunInjections::run(RiverRun::find($ids['river_run_id']));
    expect($injections['river']()->id)->toBe($ids['river_id'])
        ->and($injections['riverRun']()->id)->toBe($ids['river_run_id'])
        ->and($injections['run']()->id)->toBe($ids['river_run_id'])
        ->and($injections)->toHaveKey('map')
        ->and($injections['map']())->toBeInstanceOf(RiverMap::class);
});

it('should provide raft and sweeps', function () {
    $ids = createRun();

    $injections = GetRiverRunInjections::run(RiverRun::find($ids['river_run_id']));
    expect($injections)->toHaveKey('raft')
        ->and($injections['raft']()->name)->toBe('John Smith')
        ->and($injections)->toHaveKey('test')
        ->and($injections['test']()->name)->toBe('John Smith')
        ->and($injections)->toHaveKey('father')
        ->and($injections['father']()->name)->toBe('Joe Smith')
        ->and($injections)->toHaveKey('mother')
        ->and($injections['mother']()->name)->toBe('Mary Smith');
});

it('should not provide sweeps', function () {
    $ids = createRun();

    $injections = GetRiverRunInjections::run(RiverRun::find($ids['river_run_id']), false);
    expect($injections['raft']()->name)->toBe('John Smith')
        ->and($injections)->not()->toHaveKey('father')
        ->and($injections)->not()->toHaveKey('mother');
});

it('should not provide additional rafts as defined by main raft', function () {
    $river = River::create([
        'title' => 'Test',
        'map' => new RiverMap,
    ]);

    $article = Article::factory()->create();

    $run = RiverRun::create([
        'river_id' => $river->id,
        'raft' => new ArticleRaft(['modelId' => $article->id]),
    ]);

    $injections = GetRiverRunInjections::run(RiverRun::find($run->id));
    expect($injections)->toHaveKey('raft')
        ->and($injections['raft']()->title)->toBe($article->title)
        ->and($injections)->toHaveKey('article')
        ->and($injections['article']()->title)->toBe($article->title)
        ->and($injections)->toHaveKey('author')
        ->and($injections['author']()->name)->toBe($article->user->name);
});

function createRun(): array
{
    $river = River::create([
        'title' => 'Test',
        'map' => new RiverMap,
    ]);

    $run = RiverRun::create([
        'river_id' => $river->id,
        'raft' => new TestRaft(['name' => 'John Smith']),
        'sweeps' => [
            'father' => new TestRaft(['name' => 'Joe Smith']),
            'mother' => new TestRaft(['name' => 'Mary Smith']),
        ],
    ]);

    return [
        'river_id' => $river->id,
        'river_run_id' => $run->id,
    ];
}
