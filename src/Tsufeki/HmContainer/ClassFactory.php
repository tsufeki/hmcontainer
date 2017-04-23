<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class ClassFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string[]
     */
    private $dependencies;

    /**
     * @param Wiring $wiring
     * @param string $class
     * @param array|null $dependencies Ids of services to be injected to the constructor.
     *                                 If not provided, they are guessed from typehints.
     *
     * @throws ParameterNotWiredException
     */
    public function __construct(Wiring $wiring, string $class, array $dependencies = null)
    {
        $this->class = $class;
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
        try {
            $reflectionClass = new ReflectionClass($this->class);
            $constructor = $reflectionClass->getConstructor();

            return $constructor !== null ? $wiring->findDependencies($constructor) : [];
        } catch (ReflectionException $e) {
            throw new ParameterNotWiredException($e->getMessage());
        }
    }

    public function create(ContainerInterface $container)
    {
        $class = $this->class;
        $args = [];
        foreach ($this->dependencies as $dep) {
            $args[] = $container->get($dep);
        }

        return new $class(...$args);
    }
}
