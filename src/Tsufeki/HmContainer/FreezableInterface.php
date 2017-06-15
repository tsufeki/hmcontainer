<?php

namespace Tsufeki\HmContainer;

interface FreezableInterface
{
    public function freeze();

    /**
     * @return bool
     */
    public function isFrozen(): bool;
}
