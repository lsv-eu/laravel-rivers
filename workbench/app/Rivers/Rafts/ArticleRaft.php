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

    protected function propertyIsPublished(): bool
    {
        return (bool) $this->getRawProperty('published_at');
    }
}
