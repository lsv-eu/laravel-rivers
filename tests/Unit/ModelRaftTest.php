<?php

namespace Tests\Unit;

use Workbench\App\Models\Article;
use Workbench\App\Rivers\Rafts\ArticleRaft;

test('can get a property by name', function () {
    $article = Article::factory()->create();
    expect((new ArticleRaft(['modelId' => $article->id]))->title)->toEqual($article->title);
});

test('can get a dynamic property by name', function () {
    $article = Article::factory()->create();
    expect((new ArticleRaft(['modelId' => $article->id]))->isPublished)->toEqual(true);
});

test('can get a nested property', function () {
    $article = Article::factory()->create();
    expect((new ArticleRaft(['modelId' => $article->id]))->getProperty('user.name'))->toEqual($article->user->name);
});
