<?php

namespace LsvEu\Rivers\Cartography\Source;

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\Source;

class ModelCreated extends Source
{
    public string $class;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->class = $attributes['class'];
    }

    public function createRaft(?Model $record = null): Model
    {
        return $record;
    }

    public function getStartListener(): ?string
    {
        return "model.created.$this->class";
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'class' => $this->class,
        ];
    }
}
