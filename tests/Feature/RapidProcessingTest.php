<?php

namespace Tests\Unit;

use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\Ripple;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Feature\Classes\BasicUserMap;
use Tests\Feature\Classes\NameRaft;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

it('should process ripples with raft changes', function () {
    $river = River::create([
        'title' => 'Rapid Processing Test',
        'map' => new BasicUserMap,
    ]);

    $user = User::factory()->createQuietly(['name' => 'John Smith']);
    $run = $river->riverRuns()->create(['raft' => new UserRaft(['modelId' => $user->id])]);

    $rapid = new Rapid([
        'ripples' => [
            new class extends Ripple
            {
                public function process(?UserRaft $raft = null): void
                {
                    $user = $raft->getUser();
                    $user->update(['name' => strtolower($user->name)]);
                }
            },
            new class extends Ripple
            {
                public function process(?UserRaft $raft = null): void
                {
                    $user = $raft->getUser();
                    $user->update(['name' => strtoupper($user->name)]);
                }
            },
        ],
    ]);

    expect($user->name)->toBe('John Smith');
    EvaluateRiverElement::run($run, $rapid, 'process');
    $user->refresh();
    expect($user->name)->toBe('JOHN SMITH');
});

it('should process rapids with sweep changes', function () {
    $river = River::create([
        'title' => 'Rapid Processing Test',
        'map' => new BasicUserMap,
    ]);

    $user = User::factory()->createQuietly(['name' => 'John Smith']);
    $run = $river->riverRuns()->create(['raft' => new UserRaft(['modelId' => $user->id])]);

    $rapid = new Rapid([
        'ripples' => [
            new class extends Ripple
            {
                public function process(?RiverRun $run = null, ?UserRaft $raft = null): void
                {
                    $run->sweeps->put('name', new NameRaft(['name' => $raft->name]));
                    $run->save();
                }
            },
            new class extends Ripple
            {
                public function process(?RiverRun $run = null, ?NameRaft $name = null): void
                {
                    $name->name = strtoupper($name->name);
                }
            },
        ],
    ]);

    expect($user->name)->toBe('John Smith');
    EvaluateRiverElement::run($run, $rapid, 'process');
    $user->refresh();
    expect($run->sweeps->get('name')?->name)->toBe('JOHN SMITH');
    $run->refresh();
    expect($run->sweeps->get('name')?->name)->toBe('JOHN SMITH');
});
