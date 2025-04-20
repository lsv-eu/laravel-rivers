<?php

namespace LsvEu\Rivers\Cartography;

class Ripple
{
    public static function fromArray($ripple)
    {
        return new self($ripple['id'], $ripple['name'], $ripple['description']);
    }
}
