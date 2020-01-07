<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

final class PersistenceService implements Persistence
{
    private $opentracing;

    public function __construct(Opentracing $opentracing)
    {
        $this->opentracing = $opentracing;
    }

    public function flush(): void
    {
        $this->opentracing->getTracerInstance()->flush();
    }
}
