<?php

namespace LsvEu\Rivers\Cartography\Source\Conditions;

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\RiverElement;

abstract class Condition extends RiverElement
{
    abstract public function check(Model $model): bool;
}
