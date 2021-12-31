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
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StartRootSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private $spanOptionsFactory;
    private $kernel;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->spanOptionsFactory = $this->prophesize(SpanOptionsFactory::class);
        $this->kernel = $this->prophesize(HttpKernelInterface::class);

        $this->subject = new StartRootSpanSubscriber($this->tracing->reveal(), $this->spanOptionsFactory->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.request', $this->subject::getSubscribedEvents());
    }

    public function testOnRequestIsMasterRequest(): void
    {
        $request = Request::create('http://some.uri.test/');
        $event = new KernelEvent($this->kernel->reveal(), $request, HttpKernelInterface::MASTER_REQUEST);

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

        $this->subject->onRequest($event);
    }

    public function testOnRequestIsNotMasterRequest(): void
    {
        $event = new KernelEvent($this->kernel->reveal(), $this->prophesize(Request::class)->reveal(), HttpKernelInterface::SUB_REQUEST);

        $this->spanOptionsFactory->createSpanOptions(Argument::any())->shouldNotBeCalled();
        $this->tracing->startActiveSpan(Argument::cetera())->shouldNotBeCalled();

        $this->subject->onRequest($event);
    }
}
