<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

class CallableFactory implements FactoryInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable Must accept single argument of type ContainerInterface
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function create(ContainerInterface $container)
    {
        return ($this->callable)($container);
    }
}
