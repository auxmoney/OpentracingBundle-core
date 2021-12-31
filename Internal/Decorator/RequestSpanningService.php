<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal\Decorator;

use Auxmoney\OpentracingBundle\Service\Tracing;

use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_STATUS_CODE;
use const OpenTracing\Tags\HTTP_URL;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_CLIENT;

final class RequestSpanningService implements RequestSpanning
{
    private Tracing $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function start(string $requestMethod, string $requestUrl): void
    {
        $this->tracing->startActiveSpan('sending HTTP request');
        $this->tracing->setTagOfActiveSpan(SPAN_KIND, SPAN_KIND_RPC_CLIENT);
        $this->tracing->setTagOfActiveSpan(HTTP_METHOD, $requestMethod);
        $this->tracing->setTagOfActiveSpan(HTTP_URL, $requestUrl);
    }

    public function finish(int $responseStatusCode): void
    {
        $this->tracing->setTagOfActiveSpan(HTTP_STATUS_CODE, $responseStatusCode);
        if ($responseStatusCode >= 400) {
            $this->tracing->setTagOfActiveSpan(ERROR, true);
        }
    }
}
