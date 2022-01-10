<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use OpenTracing\SpanContext;
use OpenTracing\Tracer;

use const OpenTracing\Formats\TEXT_MAP;

final class UtilityService implements Utility
{
    private Tracer $tracer;

    public function __construct(Opentracing $opentracing)
    {
        $this->tracer = $opentracing->getTracerInstance();
    }

    public function extractSpanContext(array $headers): ?SpanContext
    {
        return $this->tracer->extract(TEXT_MAP, $headers);
    }
}
