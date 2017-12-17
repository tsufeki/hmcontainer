<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class FrozenException extends \Exception implements ContainerExceptionInterface
{
    public function __construct(string $msg = 'Container is frozen')
    {
        parent::__construct($msg);
    }
}
