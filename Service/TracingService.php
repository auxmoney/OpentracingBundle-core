<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Service;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use const OpenTracing\Formats\TEXT_MAP;

final class TracingService implements Tracing
{
    private $opentracing;
    private $logger;

    public function __construct(Opentracing $opentracing, LoggerInterface $logger)
    {
        $this->opentracing = $opentracing;
        $this->logger = $logger;
    }

    public function injectTracingHeaders(RequestInterface $request): RequestInterface
    {
        if (!$this->opentracing->getTracerInstance()->getActiveSpan()) {
            $this->logger->warning(self::class . ': could not inject tracing headers, missing active span');
            return $request;
        }

        $headers = [];
        $this->opentracing->getTracerInstance()->inject(
            $this->opentracing->getTracerInstance()->getActiveSpan()->getContext(),
            TEXT_MAP,
            $headers
        );
        foreach ($headers as $headerKey => $headerValue) {
            $request = $request->withHeader($headerKey, $headerValue);
        }
        return $request;
    }

    public function startActiveSpan(string $operationName, array $options = null): void
    {
        $options = $options ?? [];
        $options['finish_span_on_close'] = true;
        $this->opentracing->getTracerInstance()->startActiveSpan(
            $operationName,
            $options
        );
    }

    public function logInActiveSpan(array $fields): void
    {
        if (!$this->opentracing->getTracerInstance()->getActiveSpan()) {
            $this->logger->warning(self::class . ': could not log in active span, missing active span');
            return;
        }

        $this->opentracing->getTracerInstance()->getActiveSpan()->log($fields);
    }

    public function setTagOfActiveSpan(string $key, $value): void
    {
        if (!$this->opentracing->getTracerInstance()->getActiveSpan()) {
            $this->logger->warning(self::class . ': could not log in active span, missing active span');
            return;
        }

        $this->opentracing->getTracerInstance()->getActiveSpan()->setTag($key, $value);
    }

    public function finishActiveSpan(): void
    {
        if (!$this->opentracing->getTracerInstance()->getScopeManager()->getActive()) {
            $this->logger->warning(self::class . ': could not finish active span, missing active scope');
            return;
        }

        $this->opentracing->getTracerInstance()->getScopeManager()->getActive()->close();
    }
}
