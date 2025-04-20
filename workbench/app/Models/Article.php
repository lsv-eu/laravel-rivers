<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Observers\RiversObserver;
use Workbench\App\Rivers\Rafts\ArticleRaft;
use Workbench\Database\Factories\ArticleFactory;

#[ObservedBy(RiversObserver::class)]
class Article extends Model implements CreatesRaft
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

    public function createRaft(): Raft
    {
        return new ArticleRaft(['modelId' => $this->id]);
    }

    protected static function newFactory(): ArticleFactory
    {
        return new ArticleFactory;
    }
}
