<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Exception;
use Throwable;

final class EventWithThrowable
{
    public function getThrowable(): Throwable
    {
        return new Exception('throwable');
    }
}
