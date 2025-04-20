<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiverRun extends Model
{
    use HasUlids;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (RiverRun $run) {
            $run->listeners = array_values($run->river->map->getInterruptListeners($run->details));
        });
    }

    protected function casts(): array
    {
        return [
            'details' => 'json',
            'listeners' => 'json',
        ];
    }

    public function interrupts(): HasMany
    {
        return $this->riverInterrupts();
    }

    public function river(): BelongsTo
    {
        return $this->belongsTo(River::class);
    }

    public function riverInterrupts(): HasMany
    {
        return $this->hasMany(RiverInterrupt::class);
    }

    public function scopeHasListener(Builder $query, string $event): void
    {
        $query->whereJsonContains('listeners', $event);
    }
}
