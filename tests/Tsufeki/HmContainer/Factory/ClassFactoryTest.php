<?php

namespace Tsufeki\HmContainer\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Exception\ParameterNotWiredException;

/**
 * @covers \Tsufeki\HmContainer\Factory\ClassFactory
 */
class ClassFactoryTest extends TestCase
{
    public function test_instantiates_object_with_autowiring()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->once())
            ->method('findDependencies')
            ->with($this->logicalAnd(
                $this->attributeEqualTo('name', '__construct'),
                $this->attributeEqualTo('class', ClassWithContructor::class)
            ))
            ->willReturn(['xkey']);

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $classFactory = new ClassFactory($wiring, ClassWithContructor::class);
        /** @var ClassWithContructor */
        $obj = $classFactory->create($c);

        $this->assertInstanceOf(ClassWithContructor::class, $obj);
        $this->assertSame([42], $obj->args);
    }

    public function test_instantiates_object_with_autowiring_no_constructor()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->never())
            ->method('findDependencies');

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->never())
            ->method('get');

        $classFactory = new ClassFactory($wiring, ClassWithoutContructor::class);
        /** @var ClassWithoutContructor */
        $obj = $classFactory->create($c);

        $this->assertInstanceOf(ClassWithoutContructor::class, $obj);
    }

    public function test_instantiates_object_without_autowiring()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->never())
            ->method('findDependencies');

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $classFactory = new ClassFactory($wiring, ClassWithContructor::class, ['xkey']);
        /** @var ClassWithContructor */
        $obj = $classFactory->create($c);

        $this->assertInstanceOf(ClassWithContructor::class, $obj);
        $this->assertSame([42], $obj->args);
    }

    public function test_throws_when_autowiring_nonexistent_class()
    {
        $wiring = $this->createMock(Wiring::class);
        $c = $this->createMock(ContainerInterface::class);

        $this->expectException(ParameterNotWiredException::class);
        $classFactory = new ClassFactory($wiring, 'NonExistentClass');
    }
}

class ClassWithContructor
{
    public $args;

    public function __construct(...$args)
    {
        $this->args = $args;
    }
}

class ClassWithoutContructor { }
