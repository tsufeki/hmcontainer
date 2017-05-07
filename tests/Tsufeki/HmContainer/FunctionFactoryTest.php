<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Tsufeki\HmContainer\FunctionFactory
 */
class FunctionFactoryTest extends TestCase
{
    public function test_calls_function_with_autowiring()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->once())
            ->method('findDependencies')
            ->willReturn(['xkey']);

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $functionFactory = new FunctionFactory($wiring, function ($x) { return $x + 1; });
        $result = $functionFactory->create($c);

        $this->assertSame(43, $result);
    }

    public function test_function_without_autowiring()
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

        $functionFactory = new FunctionFactory($wiring, function ($x) { return $x + 1; }, ['xkey']);
        $result = $functionFactory->create($c);

        $this->assertSame(43, $result);
    }

    public function factoryMethod($x)
    {
        return $x + 1;
    }

    public function test_calls_method_with_autowiring()
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->once())
            ->method('findDependencies')
            ->willReturn(['xkey']);

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $functionFactory = new FunctionFactory($wiring, [$this, 'factoryMethod']);
        $result = $functionFactory->create($c);

        $this->assertSame(43, $result);
    }
}
