<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

class MixedMultiException extends \Exception implements ContainerExceptionInterface
{
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf("Can't mix different multi settings for id %s", $id));
    }
}
