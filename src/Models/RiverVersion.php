<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiverVersion extends Model
{
    use HasUlids;

    protected $guarded = [];

    public function river(): BelongsTo
    {
        return $this->belongsTo(River::class);
    }
}
