<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Throwable;

final class ExceptionLogSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_ERROR_MESSAGE = "No error message given";

    private Tracing $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * @return array<string,array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => ['onException', 16],
            'console.error' => ['onException', 16],
        ];
    }

    /**
     * @param ExceptionEvent|ConsoleErrorEvent $event
     */
    public function onException($event): void
    {
        $exception = $this->extractExceptionFrom($event);
        $this->tracing->logInActiveSpan(
            [
                'event' => 'error',
                'error.kind' => 'Exception',
                'error.object' => get_class($exception),
                'message' => $exception->getMessage() ?: self::DEFAULT_ERROR_MESSAGE,
                'stack' => $exception->getTraceAsString(),
            ]
        );
    }

    /**
     * @param ExceptionEvent|ConsoleErrorEvent $event
     */
    private function extractExceptionFrom($event): Throwable
    {
        if ($event instanceof ExceptionEvent) {
            return $event->getThrowable();
        }
        return $event->getError();
    }
}
