<?php

namespace Tsufeki\HmContainer;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf("Can't find id %s", $id));
    }
}
