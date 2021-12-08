<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\StartRootSpanSubscriber;
use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class StartRootSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private $spanOptionsFactory;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->spanOptionsFactory = $this->prophesize(SpanOptionsFactory::class);

        $this->subject = new StartRootSpanSubscriber($this->tracing->reveal(), $this->spanOptionsFactory->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.request', $this->subject::getSubscribedEvents());
    }

    public function testOnRequestIsMasterRequest(): void
    {
        $request = Request::create('http://some.uri.test/');
        $event = $this->prophesize(KernelEvent::class);
        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);

        $this->spanOptionsFactory->createSpanOptions($request)->willReturn(['some' => 'options']);
        $this->tracing->startActiveSpan(
            'http://some.uri.test/',
            [
                'some' => 'options',
                'tags' => [
                    'http.method' => 'GET',
                    'http.url' => 'http://some.uri.test/',
                    'span.kind' => 'server',
                    'auxmoney-opentracing-bundle.span-origin' => 'core:request'
                ]
            ]
        )->shouldBeCalledOnce();

        $this->subject->onRequest($event->reveal());
    }

    public function testOnRequestIsNotMasterRequest(): void
    {
        $event = $this->prophesize(KernelEvent::class);
        $event->isMasterRequest()->willReturn(false);

        $this->spanOptionsFactory->createSpanOptions(Argument::any())->shouldNotBeCalled();
        $this->tracing->startActiveSpan(Argument::cetera())->shouldNotBeCalled();

        $this->subject->onRequest($event->reveal());
    }
}
