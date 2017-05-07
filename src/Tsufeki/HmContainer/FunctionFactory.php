<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionMethod;

class FunctionFactory implements FactoryInterface
{
    /**
     * @var callable
     */
    private $function;

    /**
     * @var string[]|null
     */
    private $dependencies;

    /**
     * @param Wiring     $wiring
     * @param callable   $function
     * @param array|null $dependencies Ids of services to be injected to the constructor.
     *                                 If not provided, they are guessed from typehints.
     *
     * @throws ParameterNotWiredException
     */
    public function __construct(Wiring $wiring, callable $function, array $dependencies = null)
    {
        $this->function = $function;
        $this->dependencies = $dependencies ?? $this->findDependencies($wiring);
    }

    /**
     * @param Wiring $wiring
     *
     * @return string[]
     *
     * @throws ParameterNotWiredException
     */
    private function findDependencies(Wiring $wiring): array
    {
        // callable typehint in constructor ensures there won't be a ReflectionException here
        $reflectionFunction = is_array($this->function)
            ? new ReflectionMethod($this->function[0], $this->function[1])
            : new ReflectionFunction($this->function);

        return $wiring->findDependencies($reflectionFunction);
    }

    public function create(ContainerInterface $container)
    {
        $args = [];
        foreach ($this->dependencies as $dep) {
            $args[] = $container->get($dep);
        }

        return ($this->function)(...$args);
    }
}
