<?php

namespace LsvEu\Rivers\Contracts;

use ReflectionMethod;

abstract class Raft
{
    protected array $properties;

    public function __construct(array $data) {}

    final public function __get(string $name)
    {
        if ($this->hasProperty($name)) {
            return $this->getProperty($name);
        }

        throw new \Exception('Property does not exist. '.get_class($this)." does not have property, $name.");
    }

    final public function deyhdrate(): array
    {
        return [
            get_class($this),
            $this->toArray(),
        ];
    }

    public static function hydrate(array $data): self
    {
        return new static($data);
    }

    abstract public function toArray(): array;

    public function getProperty(string $name): mixed
    {
        if (! $this->hasProperty($name)) {
            return null;
        }

        $functionName = 'property'.str($name)->camel();
        if (method_exists($this, $functionName) && (new ReflectionMethod($this, $functionName))->isProtected()) {
            return $this->{$functionName}();
        }

        return $this->getRawProperty($name);
    }

    abstract protected function getRawProperty($key): mixed;

    public function getPropertyType(?string $key = null): array|null|string
    {
        return $key ? $this->properties[$key] ?? null : $this->properties;
    }

    public function hasProperty($name): bool
    {
        return array_key_exists($name, $this->properties);
    }
}
