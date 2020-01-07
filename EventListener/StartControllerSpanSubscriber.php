<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

final class StartControllerSpanSubscriber implements EventSubscriberInterface
{
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => ['onController', 16],
        ];
    }

    public function onController(KernelEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        $this->tracing->startActiveSpan(
            $attributes->get('_controller'),
            [
                'tags' => [
                    'route' => $attributes->get('_route'),
                    'route_params' => json_encode($attributes->get('_route_params')),
                ]
            ]
        );
    }
}
