<?php

namespace LsvEu\Rivers\Cartography\Fork;

use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Contracts\Raft;

abstract class Condition extends RiverElement
{
    abstract public function check(Raft $raft): bool;
}
