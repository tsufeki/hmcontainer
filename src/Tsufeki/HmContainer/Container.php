<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerInterface;

class Container implements MultiContainerInterface, LockableInterface
{
    /**
     * @var ContainerInterface|MultiContainerInterface|LockableInterface|null
     */
    private $parent;

    /**
     * @var array
     */
    private $values;

    /**
     * @var FactoryInterface[][]
     */
    private $factories;

    /**
     * @var bool[]
     */
    private $multi;

    /**
     * @var bool
     */
    private $locked;

    /**
     * @var int
     */
    private $recursionCounter;

    /**
     * @param ContainerInterface|null $parent
     */
    public function __construct(ContainerInterface $parent = null)
    {
        $this->parent = $parent;
        $this->values = [];
        $this->factories = [];
        $this->multi = [];
        $this->locked = false;
        $this->recursionCounter = 0;
    }

    public function __sleep()
    {
        return ['parent', 'factories', 'multi', 'locked'];
    }

    public function __wakeup()
    {
        $this->values = [];
        $this->recursionCounter = 0;
    }

    public function lock()
    {
        $this->locked = true;
        if ($this->parent !== null && $this->parent instanceof LockableInterface) {
            $this->parent->lock();
        }
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function has($id): bool
    {
        return isset($this->values[$id]) || isset($this->factories[$id])
            || ($this->parent !== null && $this->parent->has($id));
    }

    public function get($id)
    {
        $this->lock();

        if ($this->recursionCounter > count($this->factories)) {
            throw new CircularDependencyException($id);
        }
        $this->recursionCounter++;

        try {
            if (isset($this->values[$id])) {
                return $this->values[$id];
            }

            if (isset($this->factories[$id])) {
                return $this->values[$id] = $this->instantiate($id);
            }

            if ($this->parent !== null) {
                return $this->parent->get($id);
            }

            throw new NotFoundException($id);
        } finally {
            $this->recursionCounter--;
        }
    }

    /**
     * @param string $id
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOrDefault(string $id, $default = null)
    {
        $this->lock();

        if (!$this->has($id)) {
            return $default;
        }

        return $this->get($id);
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    private function instantiate($id)
    {
        $value = [];
        foreach($this->factories[$id] as $factory) {
            $value[] = $factory->create($this);
        }

        if ($this->multi[$id]) {
            if ($this->parent !== null && $this->parent->has($id)) {
                $value = array_merge($this->parent->get($id), $value);
            }
        } else {
            $value = $value[0];
        }

        return $value;
    }

    public function isMulti(string $id)
    {
        if (isset($this->multi[$id])) {
            return $this->multi[$id];
        }

        if ($this->parent !== null) {
            if ($this->parent instanceof MultiContainerInterface) {
                return $this->parent->isMulti($id);
            }
            return $this->parent->has($id) ? false : null;
        }

        return null;
    }

    /**
     * @param string $id
     * @param FactoryInterface $factory
     * @param bool $multi
     *
     * @return $this
     *
     * @throws LockedException
     * @throws MixedMultiException
     */
    public function set(string $id, FactoryInterface $factory, bool $multi = false): self
    {
        if ($this->isLocked()) {
            throw new LockedException();
        }

        $currentMulti = $this->isMulti($id);
        if ($currentMulti !== null && $currentMulti !== $multi) {
            throw new MixedMultiException($id);
        }

        if ($multi) {
            $this->factories[$id][] = $factory;
        } else {
            $this->factories[$id] = [$factory];
        }
        $this->multi[$id] = $multi;

        return $this;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @param bool $multi
     *
     * @return $this
     *
     * @throws LockedException
     * @throws MixedMultiException
     */
    public function setValue(string $id, $value, bool $multi = false): self
    {
        return $this->set($id, new ValueFactory($value), $multi);
    }

    /**
     * @param string $class
     * @param bool $multi
     * @param string|null $realClass
     * @param array|null $manualDependencies
     *
     * @return $this
     *
     * @throws LockedException
     * @throws MixedMultiException
     */
    public function setClass(string $class, bool $multi = false, string $realClass = null, array $manualDependencies = null): self
    {
        $realClass = $realClass ?: $class;
        return $this->set($class, new ClassFactory(new Wiring(), $realClass, $manualDependencies), $multi);
    }

    /**
     * @param string $id
     * @param callable $function
     * @param bool $multi
     * @param array|null $manualDependencies
     *
     * @return $this
     *
     * @throws LockedException
     * @throws MixedMultiException
     */
    public function setFunction(string $id, callable $function, bool $multi = false, array $manualDependencies = null): self
    {
        return $this->set($id, new FunctionFactory(new Wiring(), $function, $manualDependencies), $multi);
    }

    /**
     * @param string $id
     * @param string $targetId
     * @param bool $multi
     *
     * @return $this
     *
     * @throws LockedException
     * @throws MixedMultiException
     */
    public function setAlias(string $id, string $targetId, bool $multi = false): self
    {
        return $this->set($id, new AliasFactory($targetId), $multi);
    }
}
