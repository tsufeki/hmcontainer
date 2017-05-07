<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf('Circular dependency detected while resolving %s', $id));
    }
}
