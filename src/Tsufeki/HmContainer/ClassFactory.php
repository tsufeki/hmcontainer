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
     * @var string[]|null
     */
    private $dependencies;

    /**
     * @var Wiring
     */
    private $wiring;

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
        $this->dependencies = $dependencies;
        $this->wiring = $wiring;
    }

    /**
     * @throws ParameterNotWiredException
     */
    private function findDependencies()
    {
        try {
            $reflectionClass = new ReflectionClass($this->class);
            $constructor = $reflectionClass->getConstructor();
            if ($constructor === null) {
                $this->dependencies = [];
            } else {
                $this->dependencies = $this->wiring->findDependencies($constructor);
            }
        } catch (ReflectionException $e) {
            throw new ParameterNotWiredException($e->getMessage());
        }
    }

    public function create(ContainerInterface $container)
    {
        if ($this->dependencies === null) {
            $this->findDependencies();
        }
        $class = $this->class;
        $args = [];
        foreach ($this->dependencies as $dep) {
            $args[] = $container->get($dep);
        }

        return new $class(...$args);
    }
}
