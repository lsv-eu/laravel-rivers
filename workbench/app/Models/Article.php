<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Workbench\Database\Factories\ArticleFactory;

class Article extends Model
{
    use HasFactory;

    public function tags(): morphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->whereType('article');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): ArticleFactory
    {
        return new ArticleFactory;
    }
}
