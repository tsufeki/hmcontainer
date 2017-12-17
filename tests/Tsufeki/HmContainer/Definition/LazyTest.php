<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Definition\Lazy;

/**
 * @covers \Tsufeki\HmContainer\Definition\Lazy
 */
class LazyTest extends TestCase
{
    public function test_returns_callable_wrapping_a_factory_call()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $wrappedDefinition = $this->createMock(Definition::class);
        $wrappedDefinition
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($c))
            ->willReturn($value);

        $lazyDefinition = new Lazy($wrappedDefinition);
        $result = $lazyDefinition->get($c);

        $this->assertSame($value, $result());
    }
}
