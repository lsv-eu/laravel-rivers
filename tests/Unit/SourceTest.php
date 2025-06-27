<?php

use LsvEu\Rivers\Cartography\Condition;
use LsvEu\Rivers\Cartography\Source;
use LsvEu\Rivers\Models\RiverRun;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

test('source without conditions tests true', function () {
    $source = new class extends Source {};
    $user = User::factory()->create();
    $run = new RiverRun(['raft' => $user->createRaft()]);

    expect($source->check($run))->toBeTrue();
});

test('source with a condition tests correctly', function () {
    $sourceClass = new class extends Source {};
    $condition = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->name === 'Good';
        }
    };
    $source = new $sourceClass([
        'conditions' => [
            $condition,
        ],
    ]);
    $userBad = User::factory()->create(['name' => 'Bad']);
    $userGood = User::factory()->create(['name' => 'Good']);

    $runBad = new RiverRun(['raft' => $userBad->createRaft()]);
    $runGood = new RiverRun(['raft' => $userGood->createRaft()]);

    expect($source->check($runBad))->toBeFalse()
        ->and($source->check($runGood))->toBeTrue();
});

test('source with multiple conditions tests correctly', function () {
    $sourceClass = new class extends Source {};
    $condition1 = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->name === 'Good';
        }
    };
    $condition2 = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->email === 'good@example.com';
        }
    };

    $source = new $sourceClass([
        'conditions' => [
            $condition1,
            $condition2,
        ],
    ]);

    $userBad = User::factory()->create(['name' => 'Good', 'email' => 'bad@example.com']);
    $userGood = User::factory()->create(['name' => 'Good', 'email' => 'good@example.com']);

    $runBad = new RiverRun(['raft' => $userBad->createRaft()]);
    $runGood = new RiverRun(['raft' => $userGood->createRaft()]);

    expect($source->check($runBad))->toBeFalse()
        ->and($source->check($runGood))->toBeTrue();
});
