<?php

namespace LsvEu\Rivers\Cartography;

abstract class Condition extends RiverElement
{
    abstract public function evaluate(): bool;
}
