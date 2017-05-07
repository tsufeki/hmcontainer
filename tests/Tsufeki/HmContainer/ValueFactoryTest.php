<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Tsufeki\HmContainer\ValueFactory
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
