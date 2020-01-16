<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

interface Persistence
{
    public function flush(): void;
}
