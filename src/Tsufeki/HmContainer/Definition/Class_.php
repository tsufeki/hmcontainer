<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Exception\ClassNotFoundException;
use Tsufeki\HmContainer\Wiring\Wiring;

final class Class_ implements Definition
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var Definition[]
     */
    private $arguments;

    /**
     * @param string                     $class
     * @param (Definition|string|null)[] $arguments
     */
    public function __construct(string $class, array $arguments = [], Wiring $wiring = null)
    {
        $this->class = $class;

        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ClassNotFoundException($class);
        }

        $constructor = $reflection->getConstructor();
        $this->arguments = [];
        if ($constructor !== null) {
            $this->arguments = ($wiring ?? new Wiring())->resolveArguments($constructor, $arguments);
        }
    }

    public function get(ContainerInterface $container)
    {
        $class = $this->class;
        $argumentValues = array_map(function (Definition $def) use ($container) {
            return $def->get($container);
        }, $this->arguments);

        return new $class(...$argumentValues);
    }
}
