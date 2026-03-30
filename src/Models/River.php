<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Casts\AsRiverMap;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Events\RiverPausedEvent;
use LsvEu\Rivers\Events\RiverResumedEvent;
use LsvEu\Rivers\Exceptions\InvalidRiverStatusException;

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

        static::creating(function (River $river) {
            $river->status ??= 'draft';
        });

        static::created(function (River $river) {
            $version = $river->versions()->create([
                'map' => $river->map,
                'published_at' => $river->status !== 'draft' ? now() : null,
            ]);

            $changes = [
                'working_version_id' => $version->id,
            ];
            if ($river->status !== 'draft') {
                $changes['current_version_id'] = $version->id;
            } else {
                $changes['map'] = null;
            }

            $river->updateQuietly($changes);
        });

        static::updating(function (River $river) {
            if ($river->isDirty('map')) {
                // If status is "draft"
                if ($river->status === 'draft') {
                    $river->workingVersion->update(['map' => $river->map]);
                    unset($river->workingVersion);
                    $river->map = $river->getOriginal('map');

                    // If the versions do not match and the working version is published
                } elseif (
                    (
                        $river->current_version_id !== $river->working_version_id ||
                        $river->getOriginalWithoutRewindingModel('status') === 'draft'
                    ) &&
                    $river->workingVersion()->first()->published_at
                ) {
                    $river->current_version_id = $river->working_version_id;
                    unset($river->currentVersion);

                    // If the status is not "draft" and we aren't publishing
                } else {
                    if ($river->current_version_id === $river->working_version_id) {
                        $version = $river->versions()->create(['map' => $river->map]);
                        $river->working_version_id = $version->id;
                        unset($river->workingVersion);
                    } else {
                        $river->workingVersion->update(['map' => $river->map]);
                    }
                    $river->map = $river->getOriginal('map');
                }
            }
        });

        static::saving(function (River $river) {
            $river->listeners = $river->status === 'active' ? array_values($river->map->getListenerEvents()) : [];
            $river->repeatable = $river->map->repeatable;
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
            'map' => AsRiverMap::class,
        ];
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(RiverVersion::class, 'current_version_id');
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

    public function workingVersion(): BelongsTo
    {
        return $this->belongsTo(RiverVersion::class, 'working_version_id');
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
        if ($this->status !== 'active') {
            throw new \Exception('Cannot pause river that is not paused');
        }

        $this->status = 'paused';
        $this->save();
    }

    public function publish(): void
    {
        if ($this->workingVersion->published_at) {
            throw new \Exception('Cannot publish river that is already published');
        }

        $this->workingVersion->update(['published_at' => now()]);

        if ($this->status === 'draft') {
            $this->status = 'paused';
        }

        $this->map = $this->workingVersion()->first()->map;
        $this->save();
    }

    public function resume(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function startRun(Launch $launch, Raft $raft): void
    {
        if (! $this->isPaused()) {
            $run = $this->riverRuns()->create([
                'raft' => $raft,
                'location' => $launch->id,
            ]);

            Config::get('rivers.job_class')::dispatch($run->id);
        }
    }

    protected function status(): Attribute
    {
        return Attribute::set(function (string $status) {
            if (! in_array($status, ['active', 'paused', 'draft'])) {
                throw new InvalidRiverStatusException;
            }

            if ($status === 'draft' && $this->exists()) {
                throw new InvalidRiverStatusException('Cannot set draft status on existing river');
            }

            return ['status' => $status];
        });
    }
}
