<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Value;

/**
 * @covers \Tsufeki\HmContainer\Definition\Value
 */
class ValueTest extends TestCase
{
    public function test_returns_wrapped_value()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);

        $valueDefinition = new Value($value);
        $this->assertSame($value, $valueDefinition->get($c));
    }
}
