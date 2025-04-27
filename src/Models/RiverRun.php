<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use LsvEu\Rivers\Contracts\Raft;

/**
 * @property string $id
 * @property Carbon $completed_at
 * @property River $river
 * @property Raft $raft
 * @property string[] $listeners The listeners that can trigger an interrupt
 * @property Collection<string, RiverInterrupt> $interrupts The interrupts that have been triggered
 */
class RiverRun extends Model
{
    use HasUlids;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (RiverRun $run) {
            $run->listeners = array_values($run->river->map->getInterruptListeners($run->raft));
        });
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
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

    protected function raft(): Attribute
    {
        return Attribute::make(
            get: fn (): Raft => Raft::hydrate($this->attributes['raft']),
            set: fn (Raft $raft) => ['raft' => $raft->deyhdrate()],
        );
    }
}
