<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use OpenTracing\Mock\MockSpan;
use OpenTracing\Mock\MockSpanContext;
use OpenTracing\Mock\MockTracer as OriginalMockTracer;
use OpenTracing\Scope;
use OpenTracing\ScopeManager;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\Tracer;
use const OpenTracing\Formats\TEXT_MAP;

class MockTracer implements Tracer
{
    private OriginalMockTracer $mockTracer;

    public function __construct()
    {
        $this->mockTracer = new OriginalMockTracer(
            [],
            [
                TEXT_MAP => static function (array $items): SpanContext {
                    return MockSpanContext::createAsRoot(true, $items);
                }
            ]
        );
    }

    public function getScopeManager(): ScopeManager
    {
        return $this->mockTracer->getScopeManager();
    }

    public function getActiveSpan(): ?Span
    {
        return $this->mockTracer->getActiveSpan();
    }

    public function startActiveSpan(string $operationName, $options = []): Scope
    {
        return $this->mockTracer->startActiveSpan($operationName, $options);
    }

    public function startSpan(string $operationName, $options = []): Span
    {
        return $this->mockTracer->startSpan($operationName, $options);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function inject(SpanContext $spanContext, string $format, &$carrier): void
    {
        $carrier['made_up_header'] = '1:2:3:4';
    }

    public function extract($format, $carrier): ?SpanContext
    {
        return $this->mockTracer->extract($format, $carrier);
    }

    public function flush(): void
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
