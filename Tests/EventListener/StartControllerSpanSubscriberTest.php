<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\StartControllerSpanSubscriber;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class StartControllerSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);

        $this->subject = new StartControllerSpanSubscriber($this->tracing->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.controller', $this->subject::getSubscribedEvents());
    }

    public function testOnController(): void
    {
        $request = new Request();
        $request->attributes->add(
            [
                '_controller' => 'controller name',
                '_route' => 'controller route',
                '_route_params' => ['a route' => 'param', 'and' => 5]
            ]
        );
        $event = $this->prophesize(KernelEvent::class);
        $event->getRequest()->willReturn($request);

        $this->tracing->startActiveSpan(
            'controller name',
            ['tags' => ['route' => 'controller route', 'route_params' => '{"a route":"param","and":5}', 'auxmoney-opentracing-bundle.span-origin' => 'core:controller']]
        )->shouldBeCalledOnce();

        $this->subject->onController($event->reveal());
    }

    public function testOnControllerWithoutRoute(): void
    {
        $request = new Request();
        $request->attributes->add(
            [
                '_controller' => 'controller name',
            ]
        );
        $event = $this->prophesize(KernelEvent::class);
        $event->getRequest()->willReturn($request);

        $this->tracing->startActiveSpan(
            'controller name',
            ['tags' => ['auxmoney-opentracing-bundle.span-origin' => 'core:controller']]
        )->shouldBeCalledOnce();

        $this->subject->onController($event->reveal());
    }
}
