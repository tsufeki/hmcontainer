<?php

namespace Tsufeki\HmContainer;

use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @covers \Tsufeki\HmContainer\Wiring
 * @covers \Tsufeki\HmContainer\ParameterNotWiredException
 */
class WiringTest extends TestCase
{
    private function typehintedMethod(\stdClass $x, \Exception $e) { }

    public function test_retrieves_typehint()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'typehintedMethod');

        $this->assertSame(['stdClass', 'Exception'], $wiring->findDependencies($func));
    }

    /**
     * @param \stdClass  $x
     * @param \Exception $e
     *
     * @return void
     */
    private function docBlockMethod($x, $e) { }

    public function test_retrieves_type_from_docblock()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'docBlockMethod');

        $this->assertSame(['stdClass', 'Exception'], $wiring->findDependencies($func));
    }

    /**
     * @param \stdClass[] $x
     */
    private function arrayDocBlockMethod($x) { }

    public function test_retrieves_array_type_from_docblock()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'arrayDocBlockMethod');

        $this->assertEquals([new Optional('stdClass', [])], $wiring->findDependencies($func));
    }

    /**
     * @param \stdClass $x
     */
    private function unusedParamTagMethod() { }

    public function test_ignores_unused_param_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'unusedParamTagMethod');

        $this->assertSame([], $wiring->findDependencies($func));
    }

    /**
     * @param \stdClass  $x @Inject("xkey")
     * @param \Exception $e @Inject("ekey")
     */
    private function injectTagMethod($x, $e) { }

    public function test_retrieves_key_from_inject_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'injectTagMethod');

        $this->assertSame(['xkey', 'ekey'], $wiring->findDependencies($func));
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
    ) { }

    public function test_respects_priority_of_hints()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'priorityMethod');

        $this->assertSame([
            'xkey',
            'stdClass',
            'Exception',
            'xkey',
            'stdClass',
            'xkey',
            'Exception',
        ], $wiring->findDependencies($func));
    }

    private function intTypehintMethod(int $x) { }

    public function test_doesnt_recognize_int_typehint()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'intTypehintMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    private function noTypehintMethod($x) { }

    public function test_throws_when_no_type()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'noTypehintMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    /**
     * @param int $x
     */
    private function intParamTagMethod($x) { }

    public function test_doesnt_recognize_int_param_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'intParamTagMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    /**
     * @param int[] $x
     */
    private function intArrayParamTagMethod($x) { }

    public function test_doesnt_recognize_int_array_param_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'intArrayParamTagMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    /**
     * @param array $x
     */
    private function arrayParamTagMethod($x) { }

    public function test_doesnt_recognize_array_param_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'arrayParamTagMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    /**
     * @param object $x
     */
    private function objectParamTagMethod($x) { }

    public function test_doesnt_recognize_object_param_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'objectParamTagMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }

    public function test_retrieves_types_from_closure()
    {
        $wiring = new Wiring();
        $func = new ReflectionFunction(function (\stdClass $x) { });

        $this->assertSame(['stdClass'], $wiring->findDependencies($func));
    }

    private function optionalParamMethod(\stdClass $x, $y = false, \Exception $e = null) { }

    public function test_allows_unresolved_optional_parameters()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'optionalParamMethod');

        $this->assertSame(['stdClass'], $wiring->findDependencies($func));
    }

    /**
     * @param \stdClass $x @Optional
     * @param $y @Optional()
     * @param $z @Optional @Inject("zkey")
     */
    private function optionalTagsMethod($x, \Exception $y, $z) { }

    public function test_recognizes_optional_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'optionalTagsMethod');

        /** @var Optional[] $deps */
        $deps = $wiring->findDependencies($func);

        $this->assertContainsOnlyInstancesOf(Optional::class, $deps);
        $this->assertCount(3, $deps);
        $this->assertSame('stdClass', $deps[0]->getId());
        $this->assertSame('Exception', $deps[1]->getId());
        $this->assertSame('zkey', $deps[2]->getId());
    }

    private function partialTypesMethod(\stdClass $x, \Exception $y) { }

    public function test_resolves_partial_manual_dependencies()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'partialTypesMethod');

        $this->assertSame(['stdClass', 'ykey'], $wiring->findDependencies($func, [null, 'ykey']));
    }

    /**
     * @param int $x @Optional
     */
    private function optionalIntMethod($x) { }

    public function test_throws_on_unrecognized_param_with_optional_tag()
    {
        $wiring = new Wiring();
        $func = new ReflectionMethod($this, 'optionalIntMethod');

        $this->expectException(ParameterNotWiredException::class);
        $wiring->findDependencies($func);
    }
}
