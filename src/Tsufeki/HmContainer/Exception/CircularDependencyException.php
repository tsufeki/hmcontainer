<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends \Exception implements ContainerExceptionInterface
{
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf('Circular dependency detected while resolving %s', $id));
    }
}
