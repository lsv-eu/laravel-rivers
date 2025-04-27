<?php

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\Source;
use Workbench\App\Models\User;

test('source without conditions tests true', function () {
    $source = new class extends Source {};
    $user = User::factory()->create();

    expect($source->check($user))->toBeTrue();
});

test('source with a condition tests correctly', function () {
    $sourceClass = new class extends Source {};
    $condition = new class extends Source\Conditions\Condition
    {
        public function check(Model $model): bool
        {
            return $model->name === 'Good';
        }
    };
    $source = new $sourceClass([
        'conditions' => [
            $condition,
        ],
    ]);
    $userBad = User::factory()->create(['name' => 'Bad']);
    $userGood = User::factory()->create(['name' => 'Good']);

    expect($source->check($userBad))->toBeFalse()
        ->and($source->check($userGood))->toBeTrue();
});

test('source with multiple conditions tests correctly', function () {
    $sourceClass = new class extends Source {};
    $condition1 = new class extends Source\Conditions\Condition
    {
        public function check(User|Model $model): bool
        {
            return $model->name === 'Good';
        }
    };
    $condition2 = new class extends Source\Conditions\Condition
    {
        public function check(User|Model $model): bool
        {
            return $model->email === 'good@example.com';
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

    expect($source->check($userBad))->toBeFalse()
        ->and($source->check($userGood))->toBeTrue();
});
