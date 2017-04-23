<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

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
