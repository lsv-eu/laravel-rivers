<?php

namespace LsvEu\Rivers\Cartography\Bridge;

use DateInterval;
use Exception;

class TimeDelayBridge extends TimedBridge
{
    public string $duration;

    /**
     * @throws Exception
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->duration = $attributes['duration'] ?? 'PT1D';

        // Throw error if invalid ISO 8601 duration
        new DateInterval($this->duration);
    }

    public function getDateInterval(): DateInterval
    {
        return new DateInterval($this->duration);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'duration' => $this->duration,
        ];
    }
}
