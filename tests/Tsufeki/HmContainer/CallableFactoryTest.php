<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Tsufeki\HmContainer\CallableFactory
 */
class CallableFactoryTest extends TestCase
{
    public function test_calls_wrapped_function()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $func = function (ContainerInterface $cc) use ($value, $c) {
            $this->assertSame($c, $cc);
            return $value;
        };
        $callableFactory = new CallableFactory($func);

        $this->assertSame($value, $callableFactory->create($c));
    }
}
