<?php declare(strict_types=1);

namespace Tests\Tsufeki\HmContainer\Wiring;

use PHPUnit\Framework\TestCase;
use Tsufeki\HmContainer\Definition\Optional;
use Tsufeki\HmContainer\Definition\Reference;
use Tsufeki\HmContainer\Exception\ParameterNotWiredException;
use Tsufeki\HmContainer\Wiring\Wiring;

/**
 * @covers \Tsufeki\HmContainer\Wiring\Wiring
 * @covers \Tsufeki\HmContainer\Exception\ParameterNotWiredException
 */
class WiringTest extends TestCase
{
    private function checkArgs(array $expected, $callable, array $explicitArguments = [])
    {
        $expected = array_map(function ($def) {
            return is_string($def) ? new Reference($def) : $def;
        }, $expected);

        $this->assertEquals($expected, $this->runArgs($callable, $explicitArguments));
    }

    private function runArgs($callable, array $explicitArguments = [])
    {
        if (is_array($callable)) {
            $reflection = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable);
        }

        $wiring = new Wiring();

        return $wiring->resolveArguments($reflection, $explicitArguments);
    }

    private function typehintedMethod(\stdClass $x, \Exception $e)
    {
    }

    public function test_retrieves_typehint()
    {
        $this->checkArgs(['stdClass', 'Exception'], [$this, 'typehintedMethod']);
    }

    /**
     * @param \stdClass  $x
     * @param \Exception $e
     */
    private function docBlockMethod($x, $e)
    {
    }

    public function test_retrieves_type_from_docblock()
    {
        $this->checkArgs(['stdClass', 'Exception'], [$this, 'docBlockMethod']);
    }

    /**
     * @param \stdClass[] $x
     */
    private function arrayDocBlockMethod($x)
    {
    }

    public function test_retrieves_array_type_from_docblock()
    {
        $this->checkArgs([new Optional('stdClass', [])], [$this, 'arrayDocBlockMethod']);
    }

    /**
     * @param \stdClass $x
     */
    private function unusedParamTagMethod()
    {
    }

    public function test_ignores_unused_param_tag()
    {
        $this->checkArgs([], [$this, 'unusedParamTagMethod']);
    }

    /**
     * @param \stdClass  $x @Inject("xkey")
     * @param \Exception $e @Inject("ekey")
     */
    private function injectTagMethod($x, $e)
    {
    }

    public function test_retrieves_key_from_inject_tag()
    {
        $this->checkArgs(['xkey', 'ekey'], [$this, 'injectTagMethod']);
    }

    /**
     * @param \stdClass $a @Inject("xkey")
     * @param \stdClass $b
     * @param $c
     * @param \stdClass $d @Inject("xkey")
     * @param \stdClass $e
     * @param $f @Inject("xkey")
     * @param $g
     */
    private function priorityMethod(
        \Exception $a,
        \Exception $b,
        \Exception $c,
        $d,
        $e,
        \Exception $f,
        \Exception $g
    ) {
    }

    public function test_respects_priority_of_hints()
    {
        $this->checkArgs([
            'xkey',
            'stdClass',
            'Exception',
            'xkey',
            'stdClass',
            'xkey',
            'Exception',
        ], [$this, 'priorityMethod']);
    }

    private function intTypehintMethod(int $x)
    {
    }

    public function test_doesnt_recognize_int_typehint()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'intTypehintMethod']);
    }

    private function noTypehintMethod($x)
    {
    }

    public function test_throws_when_no_type()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'noTypehintMethod']);
    }

    /**
     * @param int $x
     */
    private function intParamTagMethod($x)
    {
    }

    public function test_doesnt_recognize_int_param_tag()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'intParamTagMethod']);
    }

    /**
     * @param int[] $x
     */
    private function intArrayParamTagMethod($x)
    {
    }

    public function test_doesnt_recognize_int_array_param_tag()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'intArrayParamTagMethod']);
    }

    /**
     * @param array $x
     */
    private function arrayParamTagMethod($x)
    {
    }

    public function test_doesnt_recognize_array_param_tag()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'arrayParamTagMethod']);
    }

    /**
     * @param object $x
     */
    private function objectParamTagMethod($x)
    {
    }

    public function test_doesnt_recognize_object_param_tag()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'objectParamTagMethod']);
    }

    public function test_retrieves_types_from_closure()
    {
        $this->checkArgs(['stdClass'], function (\stdClass $x) { });
    }

    /**
     * @param \stdClass $x @Optional
     * @param $y @Optional()
     * @param $z @Optional @Inject("zkey")
     */
    private function optionalTagsMethod($x, \Exception $y, $z)
    {
    }

    public function test_recognizes_optional_tag()
    {
        $this->checkArgs([
            new Optional('stdClass'),
            new Optional('Exception'),
            new Optional('zkey'),
        ], [$this, 'optionalTagsMethod']);
    }

    /**
     * @param $y @Inject("zkey")
     */
    private function optionalValueMethod(\Exception $x = null, $y = 42)
    {
    }

    public function test_recognizes_optional_value()
    {
        $this->checkArgs([
            new Optional('Exception'),
            new Optional('zkey', 42),
        ], [$this, 'optionalValueMethod']);
    }

    private function partialTypesMethod(\stdClass $x, \Exception $y, $z)
    {
    }

    public function test_resolves_partial_manual_dependencies()
    {
        $this->checkArgs(
            ['stdClass', 'ykey', 'zkey'],
            [$this, 'partialTypesMethod'],
            [null, 'ykey', new Reference('zkey')]
        );
    }

    /**
     * @param int $x @Optional
     */
    private function optionalIntMethod($x)
    {
    }

    public function test_throws_on_unrecognized_param_with_optional_tag()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'optionalIntMethod']);
    }

    public function test_throws_on_bad_explicit_argument()
    {
        $this->expectException(ParameterNotWiredException::class);
        $this->runArgs([$this, 'optionalIntMethod'], [42]);
    }
}
