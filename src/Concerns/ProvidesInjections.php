<?php

namespace LsvEu\Rivers\Concerns;

trait ProvidesInjections
{
    public function getInjectionNames(): array
    {
        if (property_exists($this, 'provides') && is_array($this->provides)) {
            return array_keys($this->provides);
        }

        if ($name = $this->getRaftName()) {
            return [$name];
        }

        return [];
    }

    public function getInjectionTypes(): array
    {
        if (property_exists($this, 'provides') && is_array($this->provides)) {
            return array_keys($this->provides);
        }

        if ($name = $this->getRaftName()) {
            return [$name => static::class];
        }

        return [];
    }

    public function getRaftName(): ?string
    {
        if (property_exists($this, 'raftName')) {
            return $this->raftName;
        }

        return str(class_basename(static::class))
            ->replaceMatches('/Raft$/', '')
            ->lcfirst()
            ->toString();
    }

    public function resolveProvidedInjection(string $name): mixed
    {
        if (! $this->getRaftName()) {
            return null;
        }

        return match ($name) {
            $this->getRaftName() => $this,
            default => null,
        };
    }
}
