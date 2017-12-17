<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Callable_;

/**
 * @covers \Tsufeki\HmContainer\Definition\Callable_
 */
class CallableTest extends TestCase
{
    public function test_calls_with_resolved_arguments()
    {
        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $callableDefinition = new Callable_(function ($x) { return $x + 1; }, ['xkey']);
        $result = $callableDefinition->get($c);

        $this->assertSame(43, $result);
    }
}
