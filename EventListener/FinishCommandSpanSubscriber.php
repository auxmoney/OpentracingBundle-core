<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use const OpenTracing\Tags\ERROR;

final class FinishCommandSpanSubscriber implements EventSubscriberInterface
{
    private $tracing;
    private $persistence;

    public function __construct(Tracing $tracing, Persistence $persistence)
    {
        $this->tracing = $tracing;
        $this->persistence = $persistence;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'console.terminate' => ['onTerminate', -2048],
        ];
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $exitCode = $event->getExitCode();
        $this->tracing->setTagOfActiveSpan('command.exit-code', $exitCode);
        if ($exitCode != 0) {
            $this->tracing->setTagOfActiveSpan(ERROR, true);
        }
        $this->tracing->finishActiveSpan();
        $this->persistence->flush();
    }
}
