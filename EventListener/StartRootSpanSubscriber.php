<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Internal\Constant;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_URL;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

final class StartRootSpanSubscriber implements EventSubscriberInterface
{
    private Tracing $tracing;
    private SpanOptionsFactory $spanOptionsFactory;

    public function __construct(
        Tracing $tracing,
        SpanOptionsFactory $spanOptionsFactory
    ) {
        $this->tracing = $tracing;
        $this->spanOptionsFactory = $spanOptionsFactory;
    }

    /**
     * @return array<string,array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['onRequest', 4096],
        ];
    }

    public function onRequest(KernelEvent $event): void
    {
        # TODO: when Symfony 4.4 is unmaintained (November 2023), remove outer if-block in favor of isMainRequest()
        if (method_exists($event, 'isMainRequest')) {
            if (!$event->isMainRequest()) {
                return;
            }
        } elseif (method_exists($event, 'isMasterRequest')) {
            if (!$event->isMasterRequest()) {
                return;
            }
        }

        $request = $event->getRequest();
        $options = $this->spanOptionsFactory->createSpanOptions($request);
        $options['tags'][HTTP_METHOD] = $request->getMethod();
        $options['tags'][HTTP_URL] = $request->getUri();
        $options['tags'][SPAN_KIND] = SPAN_KIND_RPC_SERVER;
        $options['tags'][Constant::SPAN_ORIGIN] = 'core:request';

        $this->tracing->startActiveSpan(
            $request->getUri(),
            $options
        );
    }
}
