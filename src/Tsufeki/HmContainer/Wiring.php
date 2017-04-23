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
    const UNRESOLVED = -1;
    const UNRESOLVED_OPTIONAL = -2;

    /**
     * @param ReflectionFunctionAbstract $function
     *
     * @return string[] Dependency injection keys for each parameter.
     *
     * @throws ParameterNotWiredException
     */
    public function findDependencies(ReflectionFunctionAbstract $function): array
    {
        $dependencies = $this->getParameters($function);
        $dependencies = $this->parseDocTags($dependencies, $function);

        /* TODO: optional and variadic parameters */
        $result = [];
        foreach ($dependencies as $name => $key) {
            if ($key === self::UNRESOLVED) {
                throw new ParameterNotWiredException(null, $function, $name);
            }
            if ($key === self::UNRESOLVED_OPTIONAL) {
                break;
            }
            $result[] = $key;
        }

        return $result;
    }

    /**
     * @param ReflectionFunctionAbstract $function
     *
     * @return array
     */
    private function getParameters(ReflectionFunctionAbstract $function): array
    {
        $dependencies = [];

        /** @var ReflectionParameter $param */
        foreach ($function->getParameters() as $param) {
            $key = $param->isOptional() ? self::UNRESOLVED_OPTIONAL : self::UNRESOLVED;
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
    private function parseDocTags(array $dependencies, ReflectionFunctionAbstract $function): array
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
