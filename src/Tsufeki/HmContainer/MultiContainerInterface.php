<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

interface MultiContainerInterface extends ContainerInterface
{
    /**
     * @param string $id
     *
     * @return bool|null
     */
    public function isMulti(string $id);
}
