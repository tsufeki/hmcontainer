<?php

namespace Tsufeki\HmContainer\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Tsufeki\HmContainer\Factory\AliasFactory
 */
class AliasFactoryTest extends TestCase
{
    public function test_return_aliased_value()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('target'))
            ->willReturn($value);
        $aliasFactory = new AliasFactory('target');

        $this->assertSame($value, $aliasFactory->create($c));
    }
}
