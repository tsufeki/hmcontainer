<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;

final class Optional implements Definition
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $default;

    public function __construct(string $key, $default = null)
    {
        $this->key = $key;
        $this->default = $default;
    }

    public function get(ContainerInterface $container)
    {
        if ($container->has($this->key)) {
            return $container->get($this->key);
        }

        return $this->default;
    }
}
