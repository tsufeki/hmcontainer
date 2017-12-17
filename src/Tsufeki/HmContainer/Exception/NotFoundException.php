<?php declare(strict_types=1);

namespace Tsufeki\HmContainer\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct(string $id = null)
    {
        parent::__construct(sprintf("Can't find id %s", $id));
    }
}
