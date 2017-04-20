<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerExceptionInterface;

class LockedException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string $msg
     */
    public function __construct(string $msg = 'Container is locked')
    {
        parent::__construct($msg);
    }
}
