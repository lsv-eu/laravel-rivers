<?php

namespace Workbench\App\Rivers\Sources;

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\Source\Conditions\Condition;
use Workbench\App\Models\Taggable;

class TaggableCondition extends Condition
{
    public string $tagId;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->tagId = $attributes['tagId'];
    }

    public function check(Taggable|Model $model): bool
    {
        return $model->tag_id == $this->tagId;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'tagId' => $this->tagId,
        ];
    }
}
