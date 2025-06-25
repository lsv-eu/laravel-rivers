<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

class RiverTimedBridge extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'details' => 'json',
            'paused' => 'boolean',
            'resume_at' => 'datetime',
        ];
    }

    public function riverRun(): BelongsTo
    {
        return $this->belongsTo(RiverRun::class);
    }

    public function resume(): void
    {
        $this->riverRun->update(['at_bridge' => false]);
        Config::get('rivers.job_class')::dispatch($this->river_run_id);
    }

    protected function resumeAt(): Attribute
    {
        return Attribute::set(fn (\DateTime $value) => [
            'resume_at' => $value->setTime($value->format('H'), $value->format('i')),
        ]);
    }
}
