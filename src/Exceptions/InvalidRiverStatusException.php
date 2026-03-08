<?php

namespace LsvEu\Rivers\Exceptions;

class InvalidRiverStatusException extends \Exception
{
    protected $message = 'Cannot assign an invalid status to River.';
}
