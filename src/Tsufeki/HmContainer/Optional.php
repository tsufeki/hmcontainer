<?php

namespace Tsufeki\HmContainer;

class Optional
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @param string $id
     * @param mixed  $default
     */
    public function __construct(string $id, $default = null)
    {
        $this->id = $id;
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }
}
