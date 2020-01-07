<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Mock;

use Exception;

final class EventWithException
{
    public function getException(): Exception
    {
        return new Exception('exception');
    }
}
