<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Wiring;

class Argument
{
    /**
     * @var string|null
     */
    public $key = null;

    /**
     * @var bool
     */
    public $optional = false;

    /**
     * @var mixed
     */
    public $default = null;
}
