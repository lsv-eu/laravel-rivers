<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Exceptions\NotEvaluatableException;
use LsvEu\Rivers\Models\RiverRun;
use ReflectionMethod;

class EvaluateRiverElement
{
    protected array $injections;

    public function __construct(protected RiverRun $run)
    {
        $this->injections = GetRiverRunInjections::run($run);
    }

    public static function run(RiverRun $run, RiverElement $element, string $method = 'evaluate'): mixed
    {
        return (new static($run))->handle($element, $method);
    }

    public function handle(RiverElement $element, string $method = 'evaluate'): mixed
    {
        if (! method_exists($element, $method)) {
            throw new (NotEvaluatableException::class);
        }

        $dependencies = [];

        foreach ((new ReflectionMethod($element::class, $method))->getParameters() as $parameter) {
            $dependencies[] = $this->injections[$parameter->name]() ?? null;
        }

        return $element->$method(...$dependencies);
    }
}
