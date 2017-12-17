<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Definition\Multi;

/**
 * @covers \Tsufeki\HmContainer\Definition\Multi
 */
class MultiTest extends TestCase
{
    private function getWrappedDefinition($value, ContainerInterface $c)
    {
        $wrappedDefinition = $this->createMock(Definition::class);
        $wrappedDefinition
            ->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($c))
            ->willReturn($value);

        return $wrappedDefinition;
    }

    public function test_returns_array_of_wrapped_definitions()
    {
        $c = $this->createMock(ContainerInterface::class);

        $valueA = new \stdClass();
        $valueB = new \stdClass();

        $multiDefinition = new Multi($this->getWrappedDefinition($valueA, $c));
        $multiDefinition->add($this->getWrappedDefinition($valueB, $c));

        $result = $multiDefinition->get($c);

        $this->assertSame([$valueA, $valueB], $result);
    }
}
