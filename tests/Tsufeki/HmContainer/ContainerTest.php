<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Tsufeki\HmContainer\Container;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Exception\CircularDependencyException;
use Tsufeki\HmContainer\Exception\FrozenException;
use Tsufeki\HmContainer\Exception\MixedMultiException;
use Tsufeki\HmContainer\Exception\NotFoundException;

/**
 * @covers \Tsufeki\HmContainer\Container
 * @covers \Tsufeki\HmContainer\Exception\CircularDependencyException
 * @covers \Tsufeki\HmContainer\Exception\FrozenException
 * @covers \Tsufeki\HmContainer\Exception\MixedMultiException
 * @covers \Tsufeki\HmContainer\Exception\NotFoundException
 */
class ContainerTest extends TestCase
{
    private function makeDefinition($value, $required = true)
    {
        $factory = $this->createMock(Definition::class);
        $factory
            ->expects($required ? $this->once() : $this->any())
            ->method('get')
            ->with($this->isInstanceOf(Container::class))
            ->willReturn($value);

        return $factory;
    }

    public function test_gets_value()
    {
        $c = new Container();
        $c->set('id', $this->makeDefinition(42));

        $this->assertTrue($c->has('id'));
        $this->assertSame(42, $c->get('id'));
    }

    public function test_gets_cached_value()
    {
        $obj = new \stdClass();
        $c = new Container();
        $c->set('id', $this->makeDefinition($obj));

        $this->assertSame($obj, $c->get('id'));
        $this->assertTrue($c->has('id'));
        $this->assertSame($obj, $c->get('id'));
    }

    public function test_throws_when_not_found()
    {
        $c = new Container();

        $this->assertFalse($c->has('id'));
        $this->expectException(NotFoundException::class);
        $c->get('id');
    }

    public function test_freezes()
    {
        $c = new Container();
        $factory = $this->makeDefinition(0, false);

        $this->assertFalse($c->isFrozen());
        $c->freeze();

        $this->assertTrue($c->isFrozen());
        $this->expectException(FrozenException::class);
        $c->set('id', $factory);
    }

    public function test_freezes_when_getting()
    {
        $c = new Container();
        $c->set('id', $this->makeDefinition(42));

        $this->assertFalse($c->isFrozen());
        $c->get('id');
        $this->assertTrue($c->isFrozen());
    }

    public function test_gets_default_value()
    {
        $c = new Container();
        $c->set('id', $this->makeDefinition(42));

        $this->assertSame(42, $c->getOrDefault('id', 53));
        $this->assertSame(53, $c->getOrDefault('not-existent', 53));
    }

    public function test_replaces_values()
    {
        $c = new Container();
        $c->set('id', $this->makeDefinition(42, false));
        $c->set('id', $this->makeDefinition(53));

        $this->assertTrue($c->has('id'));
        $this->assertSame(53, $c->get('id'));
    }

    public function test_gets_multi_values()
    {
        $c = new Container();
        $c->set('id', $this->makeDefinition(42), true);
        $c->set('id', $this->makeDefinition(53), true);

        $this->assertTrue($c->has('id'));
        $this->assertTrue($c->isMulti('id'));
        $this->assertSame([42, 53], $c->get('id'));
    }

    public function test_throws_when_mixed_multi_modes()
    {
        $c = new Container();
        $factory1 = $this->makeDefinition(0, false);
        $factory2 = $this->makeDefinition(0, false);
        $c->set('id', $factory1, false);

        $this->expectException(MixedMultiException::class);
        $c->set('id', $factory2, true);
    }

    public function test_throws_when_mixed_multi_modes_2()
    {
        $c = new Container();
        $factory1 = $this->makeDefinition(0, false);
        $factory2 = $this->makeDefinition(0, false);
        $c->set('id', $factory1, true);

        $this->expectException(MixedMultiException::class);
        $c->set('id', $factory2, false);
    }

    public function test_sets_value()
    {
        $c = new Container();
        $c->setValue('x', 42);
        $c->setValue('y', 53, true);
        $c->setValue('y', 54, true);

        $this->assertSame(42, $c->get('x'));
        $this->assertSame([53, 54], $c->get('y'));
    }

    public function test_sets_class()
    {
        $c = new Container();
        $c->setClass('id', \stdClass::class, true);

        $this->assertInstanceOf(\stdClass::class, $c->get('id')[0]);
    }

    public function test_sets_from_function()
    {
        $c = new Container();
        $c->setCallable('id', function () { return 42; }, [], true);

        $this->assertSame([42], $c->get('id'));
    }

    public function test_sets_alias()
    {
        $c = new Container();
        $c->setValue('x', 42);
        $c->setAlias('y', 'x', true);

        $this->assertSame([42], $c->get('y'));
    }

    public function test_throws_on_circular_dependency()
    {
        $c = new Container();
        $c->setAlias('x', 'y');
        $c->setAlias('y', 'x');

        $this->expectException(CircularDependencyException::class);
        $c->get('x');
    }

    public function test_serializes_and_unserializes()
    {
        $c = new Container();
        $c->setValue('x', 42);
        $c->setClass('y', \stdClass::class, false);
        $c->setAlias('z', 'x', true);
        $oldObject = $c->get('y');

        $serialized = serialize($c);
        /** @var Container $cc */
        $cc = unserialize($serialized);

        $this->assertSame(42, $cc->get('x'));
        $newObject = $cc->get('y');
        $this->assertSame([42], $cc->get('z'));
        $this->assertInstanceOf(\stdClass::class, $newObject);
        $this->assertNotSame($oldObject, $newObject);
    }

    public function test_adds_lazy_item()
    {
        $c = new Container();
        $c->setLazy('id', $this->makeDefinition(42));

        $this->assertSame(42, $c->get('id')());
    }
}
