<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\TracingId;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

final class FinishControllerSpanSubscriber implements EventSubscriberInterface
{
    private $tracing;
    private $logger;
    private $tracingId;
    private $returnTraceId;

    public function __construct(
        Tracing $tracing,
        TracingId $tracingId,
        LoggerInterface $logger,
        string $returnTraceId
    ) {
        $this->tracing = $tracing;
        $this->logger = $logger;
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

    /**
     * TODO: when Symfony 3.4 is unmaintained (November 2021), refactor to `ResponseEvent $event`
     * @param mixed $event FilterResponseEvent until Symfony 4.4, ResponseEvent since Symfony 4.3
     */
    public function onResponse($event): void
    {
        $attributes = $event->getRequest()->attributes;

        // This check ensures there was a span started on a corresponding kernel.controller event for this request
        if ($attributes->has('_auxmoney_controller')) {
            $response = $this->getResponse($event);

            $this->addTagsFromStatusCode($response);

            $this->addTraceIdHeader($response);

            $this->tracing->finishActiveSpan();
        }
    }

    /**
     * TODO: when Symfony 3.4 is unmaintained (November 2021), refactor to `ResponseEvent $event` and remove reflection
     * @param mixed $event FilterResponseEvent until Symfony 4.4, ResponseEvent since Symfony 4.3
     */
    private function getResponse($event): ?Response
    {
        $response = null;

        try {
            $reflectionClass = new ReflectionClass($event);
            if ($reflectionClass->hasMethod('getResponse')) {
                /** @var Response $response */
                $response = $reflectionClass->getMethod('getResponse')->invoke($event);
            }
        } catch (ReflectionException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $response;
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
