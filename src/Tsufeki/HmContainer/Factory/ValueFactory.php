<?php

namespace Tsufeki\HmContainer\Factory;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\FactoryInterface;

class ValueFactory implements FactoryInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function create(ContainerInterface $container)
    {
        return $this->value;
    }
}
