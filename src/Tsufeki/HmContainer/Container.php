<?php declare(strict_types=1);

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerExceptionInterface;
use Tsufeki\HmContainer\Exception\CircularDependencyException;
use Tsufeki\HmContainer\Exception\ClassNotFoundException;
use Tsufeki\HmContainer\Exception\FrozenException;
use Tsufeki\HmContainer\Exception\MixedMultiException;
use Tsufeki\HmContainer\Exception\NotFoundException;
use Tsufeki\HmContainer\Exception\ParameterNotWiredException;

class Container implements MultiContainerInterface
{
    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @var Definition[]
     */
    private $definitions;

    /**
     * @var bool
     */
    private $frozen;

    /**
     * @var int
     */
    private $recursionCounter;

    public function __construct()
    {
        $this->values = [];
        $this->definitions = [];
        $this->frozen = false;
        $this->recursionCounter = 0;
    }

    public function __sleep()
    {
        return ['definitions', 'frozen'];
    }

    public function __wakeup()
    {
        $this->values = [];
        $this->recursionCounter = 0;
    }

    public function freeze()
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function has($id): bool
    {
        return isset($this->values[$id]) || isset($this->definitions[$id]);
    }

    public function get($id)
    {
        $this->freeze();

        if ($this->recursionCounter > count($this->definitions)) {
            throw new CircularDependencyException($id);
        }
        $this->recursionCounter++;

        try {
            if (isset($this->values[$id])) {
                return $this->values[$id];
            }

            if (isset($this->definitions[$id])) {
                return $this->values[$id] = $this->definitions[$id]->get($this);
            }

            throw new NotFoundException($id);
        } finally {
            $this->recursionCounter--;
        }
    }

    /**
     * @param string $id
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function getOrDefault(string $id, $default = null)
    {
        $this->freeze();

        if (!$this->has($id)) {
            return $default;
        }

        return $this->get($id);
    }

    public function isMulti(string $id): bool
    {
        return isset($this->definitions[$id]) && $this->definitions[$id] instanceof Definition\Multi;
    }

    /**
     * @param string     $id
     * @param Definition $definition
     * @param bool       $multi
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     */
    public function set(string $id, Definition $definition, bool $multi = false): self
    {
        if ($this->isFrozen()) {
            throw new FrozenException();
        }

        if ($this->has($id) && $multi !== $this->isMulti($id)) {
            throw new MixedMultiException($id);
        }

        if ($multi) {
            if (!isset($this->definitions[$id])) {
                $this->definitions[$id] = new Definition\Multi();
            }

            /** @var Definition\Multi $multiDefinition */
            $multiDefinition = $this->definitions[$id];
            $multiDefinition->add($definition);
        } else {
            $this->definitions[$id] = $definition;
        }

        return $this;
    }

    /**
     * @param string $id
     * @param mixed  $value
     * @param bool   $multi
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     */
    public function setValue(string $id, $value, bool $multi = false): self
    {
        return $this->set($id, new Definition\Value($value), $multi);
    }

    /**
     * @param string                     $class
     * @param string|null                $realClass
     * @param bool                       $multi
     * @param (Definition|string|null)[] $arguments
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     * @throws ParameterNotWiredException
     * @throws ClassNotFoundException
     */
    public function setClass(string $class, string $realClass = null, bool $multi = false, array $arguments = []): self
    {
        $realClass = $realClass ?: $class;

        return $this->set($class, new Definition\Class_($realClass, $arguments), $multi);
    }

    /**
     * @param string                $id
     * @param callable              $callable
     * @param bool                  $multi
     * @param (Definition|string)[] $arguments
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     */
    public function setCallable(string $id, callable $callable, array $arguments, bool $multi = false): self
    {
        return $this->set($id, new Definition\Callable_($callable, $arguments), $multi);
    }

    /**
     * @param string $id
     * @param string $targetId
     * @param bool   $multi
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     */
    public function setAlias(string $id, string $targetId, bool $multi = false): self
    {
        return $this->set($id, new Definition\Reference($targetId), $multi);
    }

    /**
     * @param string     $id
     * @param Definition $definition
     * @param bool       $multi
     *
     * @return $this
     *
     * @throws FrozenException
     * @throws MixedMultiException
     */
    public function setLazy(string $id, Definition $definition, bool $multi = false): self
    {
        return $this->set($id, new Definition\Lazy($definition), $multi);
    }
}
