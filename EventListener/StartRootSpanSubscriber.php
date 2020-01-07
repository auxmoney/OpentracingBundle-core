<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

final class StartRootSpanSubscriber implements EventSubscriberInterface
{
    private $tracing;
    private $spanOptionsFactory;

    public function __construct(
        Tracing $tracing,
        SpanOptionsFactory $spanOptionsFactory
    ) {
        $this->tracing = $tracing;
        $this->spanOptionsFactory = $spanOptionsFactory;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => ['onRequest', 4096],
        ];
    }

    public function onRequest(KernelEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $options = $this->spanOptionsFactory->createSpanOptions($request);
        $this->tracing->startActiveSpan(
            $request->getUri(),
            $options
        );
    }
}
