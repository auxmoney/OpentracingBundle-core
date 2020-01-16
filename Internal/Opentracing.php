<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use OpenTracing\Tracer;

interface Opentracing
{
    public function getTracerInstance(): Tracer;
}
