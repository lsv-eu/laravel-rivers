<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Observers\RiversObserver;

#[ObservedBy(RiversObserver::class)]
class Taggable extends MorphPivot implements CreatesRaft
{
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createRaft(): ?Raft
    {
        if ($this->taggable && is_subclass_of($this->taggable, CreatesRaft::class)) {
            return $this->taggable->createRaft();
        }

        return null;
    }
}
