<?php

namespace LsvEu\Rivers\Contracts;

interface CanBeProcessed
{
    public function process(): void;
}
