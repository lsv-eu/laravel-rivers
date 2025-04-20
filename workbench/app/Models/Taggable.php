<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LsvEu\Rivers\Contracts\CreatesRaft;
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

    public function createRaft(): array
    {
        return [
            'modelClass' => $this->taggable_type,
            'modelId' => $this->taggable_id,
        ];
    }
}
