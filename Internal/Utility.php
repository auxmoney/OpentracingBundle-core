<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use OpenTracing\SpanContext;

interface Utility
{
    /**
     * @param array<string,string> $headers
     * @return SpanContext<string>|null
     */
    public function extractSpanContext(array $headers): ?SpanContext;
}
