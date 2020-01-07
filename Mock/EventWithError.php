<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Mock;

use Error;

final class EventWithError
{
    public function getError(): Error
    {
        return new Error('error');
    }
}
