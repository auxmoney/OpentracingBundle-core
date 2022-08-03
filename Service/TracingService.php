<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Service;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use OpenTracing\Span;
use OpenTracing\Tracer;
use OpenTracing\UnsupportedFormatException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

use const OpenTracing\Formats\TEXT_MAP;

final class TracingService implements Tracing
{
    private Tracer $tracer;
    private LoggerInterface $logger;

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

    public function getActiveSpan(): ?Span
    {
        return $this->tracer->getActiveSpan();
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
     * @throws UnsupportedFormatException when the format is not recognized by the tracer
     */
    private function doInjectTracingHeadersIntoCarrier(Span $span, array $carrier): array
    {
        $this->tracer->inject($span->getContext(), TEXT_MAP, $carrier);
        return $carrier;
    }

    public function setBaggageItem(string $key, string $value): void
    {
        $activeSpan = $this->tracer->getActiveSpan();
        if (!$activeSpan) {
            $this->logger->warning(self::class . ': could not set baggage items to active span, missing active span');
            return;
        }

        $activeSpan->addBaggageItem($key, $value);
        $activeSpan->log(
            [
                'event' => 'baggage.set',
                'key' => $key,
                'value' => $value,
            ]
        );
    }

    public function getBaggageItem(string $key): ?string
    {
        $activeSpan = $this->tracer->getActiveSpan();
        if (!$activeSpan) {
            $this->logger->warning(self::class . ': could not get baggage items from active span, missing active span');
            return null;
        }

        $baggageItem = $activeSpan->getBaggageItem($key);
        $activeSpan->log(
            [
                'event' => 'baggage.get',
                'key' => $key,
                'value' => $baggageItem,
            ]
        );
        return $baggageItem;
    }
}
