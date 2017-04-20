<?php

namespace Tsufeki\HmContainer;

use Psr\Container\ContainerExceptionInterface;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class ParameterNotWiredException extends \Exception implements ContainerExceptionInterface
{
    /**
     * @param string|null $msg
     * @param ReflectionFunctionAbstract|null $function
     * @param string|null $parameter
     */
    public function __construct(string $msg = null, ReflectionFunctionAbstract $function = null, string $parameter = null)
    {
        if (!$msg && ($function || $parameter)) {
            $msg = sprintf(
                'Can\'t wire parameter $%s of %s()',
                $parameter,
                $this->formatFunction($function)
            );
        }
        parent::__construct($msg);
    }

    /**
     * @param ReflectionFunctionAbstract $function
     *
     * @return string
     */
    private function formatFunction(ReflectionFunctionAbstract $function): string
    {
        $s = '';
        if ($function instanceof ReflectionMethod && $function->getDeclaringClass()) {
            $s .= $function->getDeclaringClass()->getName() . '::';
        }

        $s .= $function->getName();

        return $s;
    }
}
