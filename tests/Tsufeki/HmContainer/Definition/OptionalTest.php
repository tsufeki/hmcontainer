<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Optional;

/**
 * @covers \Tsufeki\HmContainer\Definition\Optional
 */
class OptionalTest extends TestCase
{
    public function test_return_referenced_value()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('target'))
            ->willReturn(true);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('target'))
            ->willReturn($value);

        $optionalDefinition = new Optional('target');
        $this->assertSame($value, $optionalDefinition->get($c));
    }

    public function test_return_default_value()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('target'))
            ->willReturn(false);
        $c
            ->expects($this->never())
            ->method('get');

        $optionalDefinition = new Optional('target', $value);
        $this->assertSame($value, $optionalDefinition->get($c));
    }
}
