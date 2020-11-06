<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Service\Tracing;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

final class ExceptionLogSubscriber implements EventSubscriberInterface
{
    const DEFAULT_ERROR_MESSAGE = "No error message given";

    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => ['onException', 16],
            'console.error' => ['onException', 16],
        ];
    }

    /**
     * TODO: when Symfony 3.4 is unmaintained (November 2021), refactor to `ExceptionEvent|ConsoleErrorEvent $event`
     * @param mixed $event GetResponseForExceptionEvent|ExceptionEvent|ConsoleErrorEvent until Symfony 4.4
     */
    public function onException($event): void
    {
        $exception = $this->extractExceptionFrom($event);
        $this->tracing->logInActiveSpan(
            [
                'event' => 'error',
                'error.kind' => 'Exception',
                'error.object' => get_class($exception),
                'message' => $exception->getMessage() ? $exception->getMessage() : self::DEFAULT_ERROR_MESSAGE,
                'stack' => $exception->getTraceAsString(),
            ]
        );
    }

    /**
     * TODO: when Symfony 3.4 is unmaintained (November 2021), refactor to `ExceptionEvent|ConsoleErrorEvent $event`
     *       and remove reflection in favor of `instanceof`
     * @param mixed $event GetResponseForExceptionEvent|ExceptionEvent|ConsoleErrorEvent until Symfony 4.4
     */
    private function extractExceptionFrom($event): Throwable
    {
        $eventException = null;
        try {
            $reflectionClass = new ReflectionClass($event);
            if ($reflectionClass->hasMethod('getException')) {
                $eventException = $reflectionClass->getMethod('getException')->invoke($event);
            }
            if ($reflectionClass->hasMethod('getThrowable')) {
                $eventException = $reflectionClass->getMethod('getThrowable')->invoke($event);
            }
            if ($reflectionClass->hasMethod('getError')) {
                $eventException = $reflectionClass->getMethod('getError')->invoke($event);
            }
        } catch (ReflectionException $exception) {
            $eventException = $exception;
        } finally {
            return $eventException ?? new ReflectionException('could not reflect event of type ' . get_class($event));
        }
    }
}
