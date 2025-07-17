<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Workbench\Database\Factories\PageFactory;

class Page extends Model
{
    use HasFactory;

    public function tags(): morphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->using(Taggable::class)->whereType('page');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): PageFactory
    {
        return new PageFactory;
    }
}
