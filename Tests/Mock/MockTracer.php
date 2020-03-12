<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use OpenTracing\Mock\MockSpan;
use OpenTracing\Mock\MockTracer as OriginalMockTracer;
use OpenTracing\SpanContext;
use OpenTracing\Tracer;
use const OpenTracing\Formats\TEXT_MAP;

final class MockTracer implements Tracer
{
    private $mockTracer;

    public function __construct()
    {
        $this->mockTracer = new OriginalMockTracer(
            [],
            [
                TEXT_MAP => 'OpenTracing\Mock\MockSpanContext::createAsRoot'
            ]
        );
    }

    public function getScopeManager()
    {
        return $this->mockTracer->getScopeManager();
    }

    public function getActiveSpan()
    {
        return $this->mockTracer->getActiveSpan();
    }

    public function startActiveSpan($operationName, $options = [])
    {
        return $this->mockTracer->startActiveSpan($operationName, $options);
    }

    public function startSpan($operationName, $options = [])
    {
        return $this->mockTracer->startSpan($operationName, $options);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function inject(SpanContext $spanContext, $format, &$carrier)
    {
        $carrier['made_up_header'] = '1:2:3:4';
    }

    public function extract($format, $carrier)
    {
        return $this->mockTracer->extract($format, $carrier);
    }

    public function flush()
    {
        $this->mockTracer->flush();
    }

    /**
     * @return MockSpan[]
     */
    public function getSpans(): array
    {
        return $this->mockTracer->getSpans();
    }
}
