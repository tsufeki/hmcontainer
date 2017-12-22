<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Wiring\Wiring;

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
     * @param callable                   $callable
     * @param (Definition|string|null)[] $arguments
     */
    public function __construct(callable $callable, array $arguments = [], Wiring $wiring = null)
    {
        $this->callable = $callable;
        $reflection = $this->getReflection($callable);

        $this->arguments = ($wiring ?? new Wiring())->resolveArguments($reflection, $arguments);
    }

    public function get(ContainerInterface $container)
    {
        $argumentValues = array_map(function (Definition $def) use ($container) {
            return $def->get($container);
        }, $this->arguments);

        return ($this->callable)(...$argumentValues);
    }

    private function getReflection($callable): \ReflectionFunctionAbstract
    {
        if (is_string($callable)) {
            if (strpos($callable, '::') !== false) {
                return new \ReflectionMethod($callable);
            }

            return new \ReflectionFunction($callable);
        }

        if (is_array($callable)) {
            return new \ReflectionMethod(...$callable);
        }

        if ($callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }

        return new \ReflectionMethod($callable, '__invoke');
    }
}
