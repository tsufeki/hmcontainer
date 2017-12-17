<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Class_;
use Tsufeki\HmContainer\Definition\Reference;
use Tsufeki\HmContainer\Exception\ClassNotFoundException;
use Tsufeki\HmContainer\Wiring\Wiring;

/**
 * @covers \Tsufeki\HmContainer\Definition\Class_
 * @covers \Tsufeki\HmContainer\Exception\ClassNotFoundException
 */
class ClassTest extends TestCase
{
    public function test_instantiates_object_with_autowiring()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->once())
            ->method('resolveArguments')
            ->with($this->logicalAnd(
                $this->attributeEqualTo('name', '__construct'),
                $this->attributeEqualTo('class', ClassWithContructor::class)
            ))
            ->willReturn([new Reference('xkey')]);

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $classDefinition = new Class_(ClassWithContructor::class, [], $wiring);
        /** @var ClassWithContructor */
        $obj = $classDefinition->get($c);

        $this->assertInstanceOf(ClassWithContructor::class, $obj);
        $this->assertSame([42], $obj->args);
    }

    public function test_instantiates_object_with_autowiring_no_constructor()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->never())
            ->method('resolveArguments');

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->never())
            ->method('get');

        $classDefinition = new Class_(ClassWithoutContructor::class, [], $wiring);
        /** @var ClassWithoutContructor */
        $obj = $classDefinition->get($c);

        $this->assertInstanceOf(ClassWithoutContructor::class, $obj);
    }

    public function test_throws_when_autowiring_nonexistent_class()
    {
        $wiring = $this->createMock(Wiring::class);
        $c = $this->createMock(ContainerInterface::class);

        $this->expectException(ClassNotFoundException::class);
        $classDefinition = new Class_('NonExistentClass', [], $wiring);
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

class ClassWithoutContructor
{
}
