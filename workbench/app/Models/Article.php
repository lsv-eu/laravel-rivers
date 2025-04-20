<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Article extends Model
{
    public function tags(): morphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->whereType('article');
    }
}
