<?php declare(strict_types=1);

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

interface MultiContainerInterface extends ContainerInterface
{
    public function isMulti(string $id): bool;
}
