<?php declare(strict_types=1);

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

interface Definition
{
    /**
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public function get(ContainerInterface $container);
}
