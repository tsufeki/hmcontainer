<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public function create(ContainerInterface $container);
}
