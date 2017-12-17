<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;

final class Callable_ implements Definition
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var Definition[]
     */
    private $arguments;

    /**
     * @param callable              $callable
     * @param (Definition|string)[] $arguments
     */
    public function __construct(callable $callable, array $arguments = [])
    {
        $this->callable = $callable;

        $this->arguments = array_map(function ($def) {
            return is_string($def) ? new Definition\Reference($def) : $def;
        }, $arguments);
    }

    public function get(ContainerInterface $container)
    {
        $argumentValues = array_map(function (Definition $def) use ($container) {
            return $def->get($container);
        }, $this->arguments);

        return ($this->callable)(...$argumentValues);
    }
}
