<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerExceptionInterface;

class MixedMultiException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf("Can't mix different multi settings for id %s", $id));
    }
}
