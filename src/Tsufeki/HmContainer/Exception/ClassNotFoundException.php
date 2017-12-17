<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class ClassNotFoundException extends \Exception implements ContainerExceptionInterface
{
    public function __construct(string $class = null)
    {
        parent::__construct(sprintf("Can't find class %s", $class));
    }
}
