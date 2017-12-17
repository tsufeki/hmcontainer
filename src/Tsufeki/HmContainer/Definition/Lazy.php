<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;

final class Lazy implements Definition
{
    /**
     * @var Definition
     */
    private $inner;

    public function __construct(Definition $inner)
    {
        $this->inner = $inner;
    }

    public function get(ContainerInterface $container)
    {
        return function () use ($container) {
            return $this->inner->get($container);
        };
    }
}
