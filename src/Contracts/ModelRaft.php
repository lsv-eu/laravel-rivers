<?php

namespace LsvEu\Rivers\Contracts;

use Illuminate\Database\Eloquent\Model;

abstract class ModelRaft extends Raft
{
    protected static string $modelClass;

    protected Model $record;

    protected string $modelId;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->modelId = $data['modelId'];
        $this->hydrateRecord();
    }

    protected function hydrateRecord(): void
    {
        $this->record = (static::$modelClass)::find($this->modelId);
    }

    public function resolveProvidedInjection(string $name): mixed
    {
        return match ($name) {
            $this->getRaftName() => new static(['modelId' => $this->modelId]),
            default => null,
        };
    }

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
        ];
    }

    protected function getRawProperty($key): mixed
    {
        return str($key)->explode('.')->reduce(fn ($carry, $key) => $carry->$key, $this->record);
    }
}
