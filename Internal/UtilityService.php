<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use OpenTracing\SpanContext;
use const OpenTracing\Formats\TEXT_MAP;

final class UtilityService implements Utility
{
    private $opentracing;

    public function __construct(Opentracing $opentracing)
    {
        $this->opentracing = $opentracing;
    }

    public function extractSpanContext(array $headers): ?SpanContext
    {
        return $this->opentracing->getTracerInstance()->extract(TEXT_MAP, $headers);
    }
}
