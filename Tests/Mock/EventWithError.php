<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Error;

final class EventWithError
{
    public function getError(): Error
    {
        return new Error('error');
    }
}
