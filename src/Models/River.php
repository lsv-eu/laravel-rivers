<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Events\RiverPausedEvent;
use LsvEu\Rivers\Events\RiverResumedEvent;
use LsvEu\Rivers\Exceptions\InvalidRiverMapException;

/**
 * @property RiverMap $map
 */
class River extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (River $river) {
            if ($river->isDirty('map')) {
                $river->versions()->create([
                    'map' => $river->map,
                ]);
            }
        });

        static::saving(function (River $river) {
            $river->listeners = $river->status == 'active' ? array_values($river->map->getStartListeners()) : [];
        });

        static::updated(function (River $river) {
            if ($river->wasChanged('status')) {
                if ($river->status === 'active' && $river->getOriginalWithoutRewindingModel('status') === 'paused') {
                    Event::dispatch(RiverResumedEvent::class, [$river]);
                } elseif ($river->status === 'paused' && $river->getOriginalWithoutRewindingModel('status') === 'active') {
                    Event::dispatch(RiverPausedEvent::class, [$river]);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'listeners' => 'json',
            'map' => RiverMap::class,
        ];
    }

    public function riverRuns(): HasMany
    {
        return $this->hasMany(RiverRun::class);
    }

    public function riverTimedBridges(): HasManyThrough
    {
        return $this->hasManyThrough(
            RiverTimedBridge::class,
            RiverRun::class,
        );
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RiverVersion::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereStatus('active');
    }

    public function scopeHasListener(Builder $query, string $event): void
    {
        $query->whereJsonContains('listeners', $event);
    }

    public function isPaused(): bool
    {
        return $this->status !== 'active';
    }

    public function pause(): void
    {
        $this->status = 'paused';
        $this->save();
    }

    public function resume(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function startRun(string $event, Raft $raft, Launch $launch): void
    {
        if (! $this->isPaused()) {
            $run = $this->riverRuns()->create([
                'raft' => $raft,
                'location' => $launch->id,
            ]);

            Config::get('rivers.job_class')::dispatch($run->id);
        }
    }

    protected function map(): Attribute
    {
        return Attribute::set(function (RiverMap $map) {
            if (! $map->isValid()) {
                throw new InvalidRiverMapException;
            }

            return $map->set($this, 'map', $map, []);
        });
    }
}
