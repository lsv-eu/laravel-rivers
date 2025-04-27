<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Cartography\Source;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Contracts\Raft;

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
            $river->listeners = array_values($river->map->getStartListeners());
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

    public function startRun(string $event, CreatesRaft|Raft $raft, Source $source): void
    {
        if (! $this->isPaused()) {
            $run = $this->riverRuns()->create([
                'raft' => $raft instanceof Raft ? $raft : $raft->createRaft(),
                'location' => $source->id,
            ]);

            $run->interrupts()->create([
                'event' => $event,
                'checked' => true,
                'details' => $raft->toArray(),
            ]);

            Config::get('rivers.job_class')::dispatch($run->id);
        }
    }
}
