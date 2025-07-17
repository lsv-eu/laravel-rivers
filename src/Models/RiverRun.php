<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LsvEu\Rivers\Contracts\Raft;

/**
 * @property string $id
 * @property Carbon $completed_at
 * @property River $river
 * @property Raft $raft
 * @property string[] $listeners The listeners that can trigger an interrupt
 * @property EloquentCollection<string, RiverInterrupt> $interrupts The interrupts that have been triggered
 * @property Collection<string, Raft> $sweeps Sweeps (additional cargo rafts)
 */
class RiverRun extends Model
{
    use HasUlids;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (RiverRun $run) {
            $run->raft_id = $run->raft->id;
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

    public function riverTimedBridge(): HasOne
    {
        return $this->hasOne(RiverTimedBridge::class);
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

    protected function sweeps(): Attribute
    {
        return Attribute::make(
            get: fn ($value): Collection => collect(json_decode($value))->map(fn ($sweep) => Raft::hydrate($sweep)),
            set: function (Collection|array|null $sweeps) {
                if (is_null($sweeps)) {
                    $sweeps = collect();
                }
                if (is_array($sweeps)) {
                    $sweeps = collect($sweeps);
                }

                if ($sweeps->keys()->contains(fn ($key) => is_int($key))) {
                    throw new \Exception('Cannot add sweep without name');
                }

                return ['sweeps' => $sweeps->map(fn (Raft $sweep) => $sweep->deyhdrate())->toJson()];
            },
        );
    }
}
