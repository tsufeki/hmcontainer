<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Tsufeki\HmContainer\LazyFactory
 */
class LazyFactoryTest extends TestCase
{
    public function test_returns_callable_wrapping_a_factory_call()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $wrappedFactory = $this->createMock(FactoryInterface::class);
        $wrappedFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($c))
            ->willReturn($value);
        $lazyFactory = new LazyFactory($wrappedFactory);

        $result = $lazyFactory->create($c);

        $this->assertSame($value, $result());
    }
}
