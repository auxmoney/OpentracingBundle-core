<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

interface TracingId
{
    public function getAsString(): string;
}
