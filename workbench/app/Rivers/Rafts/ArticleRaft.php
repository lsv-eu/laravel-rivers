<?php

namespace Workbench\App\Rivers\Rafts;

use LsvEu\Rivers\Contracts\ModelRaft;
use Workbench\App\Models\Article;

class ArticleRaft extends ModelRaft
{
    protected static string $modelClass = Article::class;

    protected array $properties = [
        'title' => 'string',
        'published_at' => 'datetime',
        'isPublished' => 'boolean',
        'user.name' => 'string',
    ];

    protected array $provides = [
        'article' => self::class,
        'author' => UserRaft::class,
    ];

    protected function propertyIsPublished(): bool
    {
        return (bool) $this->getRawProperty('published_at');
    }

    public function resolveProvidedInjection(string $name): mixed
    {
        return match ($name) {
            'article' => new static(['modelId' => $this->modelId]),
            'author' => new UserRaft(['modelId' => $this->user_id]),
            default => null,
        };
    }
}
