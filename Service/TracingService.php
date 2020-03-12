<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Service;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use OpenTracing\Exceptions\UnsupportedFormat;
use const OpenTracing\Formats\TEXT_MAP;
use OpenTracing\Span;

final class TracingService implements Tracing
{
    private $tracer;
    private $logger;

    public function __construct(Opentracing $opentracing, LoggerInterface $logger)
    {
        $this->tracer = $opentracing->getTracerInstance();
        $this->logger = $logger;
    }

    public function injectTracingHeadersIntoCarrier(array $carrier): array
    {
        $span = $this->tracer->getActiveSpan();

        if (!$span) {
            $this->logger->warning(self::class . ': could not inject tracing headers, missing active span');
            return $carrier;
        }

        return $this->doInjectTracingHeadersIntoCarrier($span, $carrier);
    }

    public function injectTracingHeaders(RequestInterface $request): RequestInterface
    {
        $span = $this->tracer->getActiveSpan();

        if (!$span) {
            $this->logger->warning(self::class . ': could not inject tracing headers, missing active span');
            return $request;
        }

        $headers = $this->doInjectTracingHeadersIntoCarrier($span, []);

        foreach ($headers as $headerKey => $headerValue) {
            $request = $request->withHeader($headerKey, $headerValue);
        }

        return $request;
    }

    public function startActiveSpan(string $operationName, array $options = null): void
    {
        $options = $options ?? [];
        $options['finish_span_on_close'] = true;
        $this->tracer->startActiveSpan(
            $operationName,
            $options
        );
    }

    public function logInActiveSpan(array $fields): void
    {
        if (!$this->tracer->getActiveSpan()) {
            $this->logger->warning(self::class . ': could not log in active span, missing active span');
            return;
        }

        $this->tracer->getActiveSpan()->log($fields);
    }

    public function setTagOfActiveSpan(string $key, $value): void
    {
        if (!$this->tracer->getActiveSpan()) {
            $this->logger->warning(self::class . ': could not log in active span, missing active span');
            return;
        }

        $this->tracer->getActiveSpan()->setTag($key, $value);
    }

    public function finishActiveSpan(): void
    {
        if (!$this->tracer->getScopeManager()->getActive()) {
            $this->logger->warning(self::class . ': could not finish active span, missing active scope');
            return;
        }

        $this->tracer->getScopeManager()->getActive()->close();
    }

    /**
     * Injects necessary tracing headers into an array.
     * @param array<mixed> $carrier
     * @return array<mixed>
     *
     * @throws UnsupportedFormat when the format is not recognized by the tracer
     */
    private function doInjectTracingHeadersIntoCarrier(Span $span, array $carrier): array
    {
        $this->tracer->inject($span->getContext(), TEXT_MAP, $carrier);
        return $carrier;
    }
}
