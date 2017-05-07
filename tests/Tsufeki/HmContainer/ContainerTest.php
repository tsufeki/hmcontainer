<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Tsufeki\HmContainer\Container
 * @covers \Tsufeki\HmContainer\Optional
 * @covers \Tsufeki\HmContainer\CircularDependencyException
 * @covers \Tsufeki\HmContainer\LockedException
 * @covers \Tsufeki\HmContainer\MixedMultiException
 * @covers \Tsufeki\HmContainer\NotFoundException
 */
class ContainerTest extends TestCase
{
    private function makeFactory($value, $required = true)
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory
            ->expects($required ? $this->once() : $this->any())
            ->method('create')
            ->with($this->isInstanceOf(Container::class))
            ->willReturn($value);

        return $factory;
    }

    public function test_gets_value()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42));

        $this->assertTrue($c->has('id'));
        $this->assertSame(42, $c->get('id'));
    }

    public function test_gets_cached_value()
    {
        $obj = new \stdClass();
        $c = new Container();
        $c->set('id', $this->makeFactory($obj));

        $this->assertSame($obj, $c->get('id'));
        $this->assertTrue($c->has('id'));
        $this->assertSame($obj, $c->get('id'));
    }

    public function test_gets_from_parent()
    {
        $parent = $this->createMock(ContainerInterface::class);
        $parent
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('id'))
            ->willReturn(42);
        $parent
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('id'))
            ->willReturn(true);

        $c = new Container($parent);

        $this->assertTrue($c->has('id'));
        $this->assertSame(42, $c->get('id'));
    }

    public function test_doesnt_get_from_parent_when_has_own()
    {
        $parent = $this->createMock(ContainerInterface::class);
        $parent
            ->expects($this->never())
            ->method('get');
        $parent
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('id'))
            ->willReturn(false);

        $c = new Container($parent);
        $c->set('id', $this->makeFactory(42));

        $this->assertTrue($c->has('id'));
        $this->assertSame(42, $c->get('id'));
    }

    public function test_throws_when_not_found()
    {
        $c = new Container();

        $this->assertFalse($c->has('id'));
        $this->expectException(NotFoundException::class);
        $c->get('id');
    }

    public function test_throws_when_not_found_in_parent()
    {
        $parent = $this->createMock(ContainerInterface::class);
        $parent
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('id'))
            ->willThrowException(new NotFoundException());
        $parent
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('id'))
            ->willReturn(false);

        $c = new Container($parent);

        $this->assertFalse($c->has('id'));
        $this->expectException(NotFoundException::class);
        $c->get('id');
    }

    public function test_locks()
    {
        $c = new Container();
        $factory = $this->makeFactory(0, false);

        $this->assertFalse($c->isLocked());
        $c->lock();

        $this->assertTrue($c->isLocked());
        $this->expectException(LockedException::class);
        $c->set('id', $factory);
    }

    public function test_locks_parent()
    {
        $parent = new Container();
        $c = new Container($parent);

        $this->assertFalse($parent->isLocked());
        $c->lock();

        $this->assertTrue($parent->isLocked());
    }

    public function test_locks_when_getting()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42));

        $this->assertFalse($c->isLocked());
        $c->get('id');
        $this->assertTrue($c->isLocked());
    }

    public function test_gets_default_value()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42));

        $this->assertSame(42, $c->getOrDefault('id', 53));
        $this->assertSame(53, $c->getOrDefault('not-existent', 53));
    }

    public function test_replaces_values()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42, false));
        $c->set('id', $this->makeFactory(53));

        $this->assertTrue($c->has('id'));
        $this->assertSame(53, $c->get('id'));
    }

    public function test_gets_multi_values()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42), ['multi' => true]);
        $c->set('id', $this->makeFactory(53), ['multi' => true]);

        $this->assertTrue($c->has('id'));
        $this->assertTrue($c->isMulti('id'));
        $this->assertSame([42, 53], $c->get('id'));
    }

    public function test_throws_when_mixed_multi_modes()
    {
        $c = new Container();
        $factory1 = $this->makeFactory(0, false);
        $factory2 = $this->makeFactory(0, false);
        $c->set('id', $factory1, ['multi' => false]);

        $this->expectException(MixedMultiException::class);
        $c->set('id', $factory2, ['multi' => true]);
    }

    public function test_throws_when_mixed_multi_modes_2()
    {
        $c = new Container();
        $factory1 = $this->makeFactory(0, false);
        $factory2 = $this->makeFactory(0, false);
        $c->set('id', $factory1, ['multi' => true]);

        $this->expectException(MixedMultiException::class);
        $c->set('id', $factory2, ['multi' => false]);
    }

    public function test_merges_multi_with_parent()
    {
        $parent = $this->createMock(MultiContainerInterface::class);
        $parent
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('id'))
            ->willReturn([42, 43]);
        $parent
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('id'))
            ->willReturn(true);
        $parent
            ->expects($this->once())
            ->method('isMulti')
            ->with($this->equalTo('id'))
            ->willReturn(true);

        $c = new Container($parent);
        $c->set('id', $this->makeFactory(53), ['multi' => true]);
        $c->set('id', $this->makeFactory(54), ['multi' => true]);

        $this->assertTrue($c->has('id'));
        $this->assertTrue($c->isMulti('id'));
        $this->assertSame([42, 43, 53, 54], $c->get('id'));
    }

    public function test_sets_value()
    {
        $c = new Container();
        $c->setValue('x', 42);
        $c->setValue('y', 53, ['multi' => true]);
        $c->setValue('y', 54, ['multi' => true]);

        $this->assertSame(42, $c->get('x'));
        $this->assertSame([53, 54], $c->get('y'));
    }

    public function test_sets_class()
    {
        $c = new Container();
        $c->setClass('id', \stdClass::class, ['multi' => true]);

        $this->assertInstanceOf(\stdClass::class, $c->get('id')[0]);
    }

    public function test_sets_from_function()
    {
        $c = new Container();
        $c->setFunction('id', function () { return 42; }, ['multi' => true]);

        $this->assertSame([42], $c->get('id'));
    }

    public function test_sets_alias()
    {
        $c = new Container();
        $c->setValue('x', 42);
        $c->setAlias('y', 'x', ['multi' => true]);

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
        $c->setClass('y', \stdClass::class, ['multi' => false]);
        $c->setAlias('z', 'x', ['multi' => true]);
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

    public function test_optional_values()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42));

        $this->assertSame(42, $c->get(new Optional('id')));
        $this->assertNull($c->get(new Optional('non-existent-id')));
    }

    public function test_adds_lazy_item()
    {
        $c = new Container();
        $c->set('id', $this->makeFactory(42), ['lazy' => true]);

        $this->assertSame(42, $c->get('id')());
    }
}
