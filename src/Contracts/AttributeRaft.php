<?php

namespace LsvEu\Rivers\Contracts;

abstract class AttributeRaft extends Raft
{
    protected array $attributes;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->attributes = $data;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected function getRawProperty($key): mixed
    {
        return $this->attributes[$key];
    }
}
