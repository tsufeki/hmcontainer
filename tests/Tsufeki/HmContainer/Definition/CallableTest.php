<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Callable_;
use Tsufeki\HmContainer\Definition\Reference;
use Tsufeki\HmContainer\Wiring\Wiring;

/**
 * @covers \Tsufeki\HmContainer\Definition\Callable_
 */
class CallableTest extends TestCase
{
    /**
     * @dataProvider callable_data
     */
    public function test_calls_with_autowiring($callable)
    {
        $wiring = $this->createMock(Wiring::class);
        $wiring
            ->expects($this->once())
            ->method('resolveArguments')
            ->willReturn([new Reference('xkey')]);

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('xkey'))
            ->willReturn(42);

        $callableDefinition = new Callable_($callable, [], $wiring);
        $result = $callableDefinition->get($c);

        $this->assertSame(43, $result);
    }

    public function callable_data(): array
    {
        return [
            [function ($x) { return $x + 1; }],
            [[new CallableFixture(), 'method']],
            [[CallableFixture::class, 'staticMethod']],
            ['Tests\\Tsufeki\\HmContainer\\Definition\\CallableFixture::staticMethod'],
            [new CallableFixture()],
            ['Tests\\Tsufeki\\HmContainer\\Definition\\functionFixture'],
        ];
    }
}

class CallableFixture
{
    public function method($x)
    {
        return $x + 1;
    }

    public static function staticMethod($x)
    {
        return $x + 1;
    }

    public function __invoke($x)
    {
        return $x + 1;
    }
}

function functionFixture($x)
{
    return $x + 1;
}
