<?php

namespace Tsufeki\HmContainer\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Tsufeki\HmContainer\Factory\ValueFactory
 */
class ValueFactoryTest extends TestCase
{
    public function test_returns_wrapped_value()
    {
        $value = new \stdClass();
        $valueFactory = new ValueFactory($value);
        $c = $this->createMock(ContainerInterface::class);

        $this->assertSame($value, $valueFactory->create($c));
    }
}
