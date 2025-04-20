<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiverInterrupt extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'details' => 'json',
        ];
    }

    public function riverRun(): BelongsTo
    {
        return $this->belongsTo(RiverRun::class);
    }
}
