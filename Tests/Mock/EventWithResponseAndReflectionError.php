<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use ReflectionException;

final class EventWithResponseAndReflectionError
{
    /**
     * @throws ReflectionException
     */
    public function getResponse(): void
    {
        throw new ReflectionException('could not get response!');
    }
}
