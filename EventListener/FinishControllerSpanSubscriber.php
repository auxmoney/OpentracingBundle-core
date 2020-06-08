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

    public function __construct(Tracing $tracing, TracingId $tracingId, LoggerInterface $logger)
    {
        $this->tracing = $tracing;
        $this->logger = $logger;
        $this->tracingId = $tracingId;
    }

    /**
     * @return array<string,array>
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
            $responseStatusCode = $response ? $response->getStatusCode() : null;
            $this->tracing->setTagOfActiveSpan(HTTP_STATUS_CODE, $responseStatusCode ?? 'not determined');
            if ($responseStatusCode && $responseStatusCode >= 400) {
                $this->tracing->setTagOfActiveSpan(ERROR, true);
            }
            // FIXME: make configurable
            if ($response) {
                $response->headers->set('X-Auxmoney-Opentracing-Trace-Id', $this->tracingId->getAsString());
            }
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
}
