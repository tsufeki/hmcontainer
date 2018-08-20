<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
        try {
            return $container->get($this->key);
        } catch (NotFoundExceptionInterface $e) {
            return $this->default;
        }
    }
}
