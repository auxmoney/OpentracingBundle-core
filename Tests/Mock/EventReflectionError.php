<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use ReflectionException;

final class EventReflectionError
{
    /**
     * @throws ReflectionException
     */
    public function getError(): void
    {
        throw new ReflectionException('this does not work');
    }
}
