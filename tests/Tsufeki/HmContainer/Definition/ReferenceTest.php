<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition\Reference;

/**
 * @covers \Tsufeki\HmContainer\Definition\Reference
 */
class ReferenceTest extends TestCase
{
    public function test_return_referenced_value()
    {
        $value = new \stdClass();
        $c = $this->createMock(ContainerInterface::class);
        $c
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('target'))
            ->willReturn($value);

        $referenceDefinition = new Reference('target');
        $this->assertSame($value, $referenceDefinition->get($c));
    }
}
