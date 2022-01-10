<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\TracingId;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

final class FinishControllerSpanSubscriber implements EventSubscriberInterface
{
    private Tracing $tracing;
    private TracingId $tracingId;
    private bool $returnTraceId;

    public function __construct(
        Tracing $tracing,
        TracingId $tracingId,
        string $returnTraceId
    ) {
        $this->tracing = $tracing;
        $this->tracingId = $tracingId;
        $this->returnTraceId = filter_var(
            $returnTraceId,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? true;
    }

    /**
     * @return array<string,array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.response' => ['onResponse', -2048],
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;

        // This check ensures there was a span started on a corresponding kernel.controller event for this request
        if ($attributes->has('_auxmoney_controller')) {
            $response = $event->getResponse();

            $this->addTagsFromStatusCode($response);

            $this->addTraceIdHeader($response);

            $this->tracing->finishActiveSpan();
        }
    }

    private function addTagsFromStatusCode(?Response $response): void
    {
        $responseStatusCode = $response ? $response->getStatusCode() : 'not determined';
        $this->tracing->setTagOfActiveSpan(HTTP_STATUS_CODE, $responseStatusCode);
        if ($responseStatusCode && (int) $responseStatusCode >= 400) {
            $this->tracing->setTagOfActiveSpan(ERROR, true);
        }
    }

    private function addTraceIdHeader(?Response $response): void
    {
        if ($response && $this->returnTraceId) {
            $response->headers->set('X-Auxmoney-Opentracing-Trace-Id', $this->tracingId->getAsString());
        }
    }
}
