<?php

namespace Tsufeki\HmContainer;

use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Object_;

class Wiring
{
    const INJECT_TAG_REGEX = '~(?:^|\s)@Inject\\(\\s*"([^"]+)"\\s*\\)~';

    /**
     * @param ReflectionFunctionAbstract $function
     *
     * @return string[] Dependency injection keys for each parameter.
     *
     * @throws ParameterNotWiredException
     */
    public function findDependencies(ReflectionFunctionAbstract $function)
    {
        $dependencies = $this->getParameters($function);
        $dependencies = $this->parseDocTags($dependencies, $function);

        /* TODO: optional and variadic parameters */
        foreach ($dependencies as $name => $key) {
            if ($key === false) {
                throw new ParameterNotWiredException(null, $function, $name);
            }
        }

        return array_values($dependencies);
    }

    /**
     * @param ReflectionFunctionAbstract $function
     *
     * @return array
     */
    private function getParameters(ReflectionFunctionAbstract $function)
    {
        $dependencies = [];

        /** @var ReflectionParameter $param */
        foreach ($function->getParameters() as $param) {
            $key = false;
            $type = $param->getType();
            if ($type !== null && !$type->isBuiltin()) {
                $key = (string)$type;
            }
            $dependencies[$param->getName()] = $key;
        }

        return $dependencies;
    }

    /**
     * @param array $dependencies
     * @param ReflectionFunctionAbstract $function
     *
     * @return array
     */
    private function parseDocTags(array $dependencies, ReflectionFunctionAbstract $function)
    {
        if (!$function->getDocComment()) {
            return $dependencies;
        }

        $context = (new ContextFactory())->createFromReflector($function);
        $docBlock = DocBlockFactory::createInstance()->create($function, $context);

        /** @var Param $paramTag */
        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            $name = $paramTag->getVariableName();
            if (!isset($dependencies[$name])) {
                continue;
            }

            /** @var Type|Object_|Array_ $type */
            $type = $paramTag->getType();
            if ($type !== null) {
                if ($type instanceof Array_) {
                    $type = $type->getValueType();
                }
                if ($type instanceof Object_ && $type->getFqsen()) {
                    $dependencies[$name] = ltrim((string)$type->getFqsen(), '\\');
                }
            }

            if (preg_match(static::INJECT_TAG_REGEX, (string)$paramTag->getDescription(), $matches)) {
                $dependencies[$name] = $matches[1];
            }
        }

        return $dependencies;
    }
}
