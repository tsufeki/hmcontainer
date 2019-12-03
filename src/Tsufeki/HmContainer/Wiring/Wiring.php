<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Wiring;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use Tsufeki\HmContainer\Definition;
use Tsufeki\HmContainer\Exception\ParameterNotWiredException;

class Wiring
{
    const INJECT_TAG_REGEX = '~(?:^|\s)@Inject\\(\\s*"([^"]+)"\\s*\\)~';
    const OPTIONAL_TAG_REGEX = '~(?:^|\s)@Optional(?:\\s|$|\\(\\s*\\))~';

    /**
     * @param ReflectionFunctionAbstract $function
     * @param (Definition|string|null)[] $explicitArguments
     *
     * @return Definition[] Dependency for each parameter.
     *
     * @throws ParameterNotWiredException
     */
    public function resolveArguments(ReflectionFunctionAbstract $function, array $explicitArguments = []): array
    {
        $arguments = $this->extractReflection($function);
        $arguments = $this->extractDocTags($arguments, $function);

        /* TODO: variadic parameters */
        /** @var Definition[] $result */
        $result = [];
        $i = 0;
        foreach ($arguments as $name => $arg) {
            $explicitArg = $explicitArguments[$i] ?? null;
            $result[] = $this->resolveArgument($function, $name, $arg, $explicitArg);

            $i++;
        }

        return $result;
    }

    private function resolveArgument(
        ReflectionFunctionAbstract $function,
        string $name,
        Argument $arg,
        $explicitArg
    ): Definition {
        if (is_object($explicitArg) && $explicitArg instanceof Definition) {
            return $explicitArg;
        }

        if (is_string($explicitArg)) {
            return new Definition\Reference($explicitArg);
        }

        if ($explicitArg !== null) {
            throw new ParameterNotWiredException("String, Definition or null required for argument '$name'");
        }

        if ($arg->key === null) {
            throw new ParameterNotWiredException(null, $function, $name);
        }

        if ($arg->optional) {
            return new Definition\Optional($arg->key, $arg->default);
        }

        return new Definition\Reference($arg->key);
    }

    /**
     * @return Argument[]
     */
    private function extractReflection(ReflectionFunctionAbstract $function): array
    {
        $arguments = [];

        /** @var ReflectionParameter $param */
        foreach ($function->getParameters() as $param) {
            $arg = new Argument();

            if ($param->isDefaultValueAvailable()) {
                $arg->optional = true;
                $arg->default = $param->getDefaultValue();
            }

            $type = $param->getType();
            if ($type !== null && !$type->isBuiltin()) {
                $arg->key = $type instanceof \ReflectionNamedType ? $type->getName() : (string)$type;
            }

            $arguments[$param->getName()] = $arg;
        }

        return $arguments;
    }

    /**
     * @param Argument[]                 $arguments
     * @param ReflectionFunctionAbstract $function
     *
     * @return Argument[]
     */
    private function extractDocTags(array $arguments, ReflectionFunctionAbstract $function): array
    {
        if (!$function->getDocComment()) {
            return $arguments;
        }

        $context = (new Types\ContextFactory())->createFromReflector($function);
        $docBlock = DocBlockFactory::createInstance()->create($function, $context);

        /** @var Param $paramTag */
        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            $name = $paramTag->getVariableName();
            if (!isset($arguments[$name])) {
                continue;
            }
            $arg = $arguments[$name];

            $type = $paramTag->getType();
            if ($type !== null) {
                if ($type instanceof Types\Array_) {
                    $type = $type->getValueType();
                    $arg->optional = true;
                    $arg->default = $arg->default ?? [];
                }

                if ($type instanceof Types\Object_ && $type->getFqsen()) {
                    $arg->key = ltrim((string)$type->getFqsen(), '\\');
                }
            }

            $paramDescription = (string)$paramTag->getDescription();

            if (preg_match(static::INJECT_TAG_REGEX, $paramDescription, $matches)) {
                $arg->key = $matches[1];
            }

            if (preg_match(static::OPTIONAL_TAG_REGEX, $paramDescription)) {
                $arg->optional = true;
            }
        }

        return $arguments;
    }
}
