<?php

namespace Tsufeki\HmContainer\Factory;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\FactoryInterface;

class LazyFactory implements FactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $wrappedFactory;

    /**
     * @param FactoryInterface $wrappedFactory
     */
    public function __construct(FactoryInterface $wrappedFactory)
    {
        $this->wrappedFactory = $wrappedFactory;
    }

    public function create(ContainerInterface $container)
    {
        return function () use ($container) {
            return $this->wrappedFactory->create($container);
        };
    }
}
