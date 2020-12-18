<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Exception;

final class EventWithExceptionWithoutErrorMessage
{
    public function getException(): Exception
    {
        return new Exception("");
    }
}
