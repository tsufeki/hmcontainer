<?php

namespace Tsufeki\HmContainer;

interface LockableInterface
{
    public function lock();

    /**
     * @return bool
     */
    public function isLocked(): bool;
}
