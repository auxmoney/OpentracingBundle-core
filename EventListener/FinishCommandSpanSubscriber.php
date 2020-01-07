<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    public static function getSubscribedEvents()
    {
        return [
            'console.terminate' => ['onTerminate', -2048],
        ];
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $this->tracing->setTagOfActiveSpan('command.exit-code', $event->getExitCode());
        $this->tracing->finishActiveSpan();
        $this->persistence->flush();
    }
}
