<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;

final class Multi implements Definition
{
    /**
     * @var Definition[]
     */
    private $definitions;

    public function __construct(Definition ...$definitions)
    {
        $this->definitions = $definitions;
    }

    public function get(ContainerInterface $container)
    {
        return array_map(function (Definition $def) use ($container) {
            return $def->get($container);
        }, $this->definitions);
    }

    /**
     * @return $this
     */
    public function add(Definition $definition): self
    {
        $this->definitions[] = $definition;

        return $this;
    }
}
