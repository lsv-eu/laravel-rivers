<?php

namespace LsvEu\Rivers\Exceptions;

class InvalidRiverMapException extends \Exception
{
    protected $message = 'Cannot assign an invalid RiverMap to River::map.';
}
