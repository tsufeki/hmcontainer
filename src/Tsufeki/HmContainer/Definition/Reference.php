<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Definition;

use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;

final class Reference implements Definition
{
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function get(ContainerInterface $container)
    {
        return $container->get($this->key);
    }
}
