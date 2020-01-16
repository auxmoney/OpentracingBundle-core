<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

final class PersistenceService implements Persistence
{
    private $tracer;

    public function __construct(Opentracing $opentracing)
    {
        $this->tracer = $opentracing->getTracerInstance();
    }

    public function flush(): void
    {
        $this->tracer->flush();
    }
}
