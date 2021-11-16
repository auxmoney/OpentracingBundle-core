<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Internal\Constant;
use Auxmoney\OpentracingBundle\Service\Tracing;
use JsonException;
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
     * @return array<string,array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => ['onController', 16],
        ];
    }

    /**
     * @throws JsonException
     */
    public function onController(KernelEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        $attributes->set('_auxmoney_controller', true);

        $tags = [
            Constant::SPAN_ORIGIN => 'core:controller',
        ];

        if ($attributes->get('_route')) {
            $tags['route'] = $attributes->get('_route');
            $tags['route_params'] = json_encode($attributes->get('_route_params'), JSON_THROW_ON_ERROR);
        }

        $this->tracing->startActiveSpan(
            $attributes->get('_controller'),
            [
                'tags' => $tags
            ]
        );
    }
}
