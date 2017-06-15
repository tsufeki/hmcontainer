<?php

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class FrozenException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string $msg
     */
    public function __construct(string $msg = 'Container is frozen')
    {
        parent::__construct($msg);
    }
}
