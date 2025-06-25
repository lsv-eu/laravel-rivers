<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Contracts\CanBeEvaluated;
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

    public static function run(RiverRun $run, string $id): mixed
    {
        return (new static($run))->handle($id);
    }

    public function handle(string $id): mixed
    {
        $element = $this->run->river->map->getElementById($id);

        if (! $element instanceof CanBeEvaluated) {
            throw new (NotEvaluatableException::class);
        }

        $dependencies = [];

        foreach ((new ReflectionMethod($element::class, 'evaluate'))->getParameters() as $parameter) {
            $dependencies[] = $this->injections[$parameter->name]() ?? null;
        }

        return $element->evaluate(...$dependencies);
    }
}
