<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

class AliasFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $target;

    /**
     * @param string $target
     */
    public function __construct(string $target)
    {
        $this->target = $target;
    }

    public function create(ContainerInterface $container)
    {
        return $container->get($this->target);
    }
}
