<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishControllerSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\TracingId;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FinishControllerSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracingId;
    private $tracing;
    private $kernel;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->tracingId = $this->prophesize(TracingId::class);
        $this->kernel = $this->prophesize(HttpKernelInterface::class);

        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            'true'
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.response', $this->subject::getSubscribedEvents());
    }

    public function testOnTerminateNoController(): void
    {
        $request = new Request();

        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->tracing->finishActiveSpan()->shouldNotBeCalled();

        $event = new ResponseEvent($this->kernel->reveal(), $request, 0, new Response());
        $this->subject->onResponse($event);

        self::assertFalse($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
    }

    public function testOnTerminateSuccessWithoutTraceId(): void
    {
        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            'false'
        );

        $request = new Request();
        $request->attributes->add(
            [
                '_controller' => 'controller name',
                '_route' => 'controller route',
                '_route_params' => ['a route' => 'param', 'and' => 5],
                '_auxmoney_controller' => true
            ]
        );

        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status_code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new ResponseEvent($this->kernel->reveal(), $request, 0, new Response());
        $this->subject->onResponse($event);

        self::assertFalse($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
    }

    public function testOnTerminateSuccess(): void
    {
        $request = new Request();
        $request->attributes->add(
            [
                '_controller' => 'controller name',
                '_route' => 'controller route',
                '_route_params' => ['a route' => 'param', 'and' => 5],
                '_auxmoney_controller' => true
            ]
        );

        $this->tracingId->getAsString()->shouldBeCalledOnce()->willReturn('tracing id');
        $this->tracing->setTagOfActiveSpan('http.status_code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new ResponseEvent($this->kernel->reveal(), $request, 0, new Response());
        $this->subject->onResponse($event);

        self::assertTrue($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
        self::assertSame('tracing id', $event->getResponse()->headers->get('X-Auxmoney-Opentracing-Trace-Id'));
    }

    public function testOnTerminateSuccessWith404(): void
    {
        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            'invalid bool'
        );

        $request = new Request();
        $request->attributes->add(
            [
                '_controller' => 'controller name',
                '_route' => 'controller route',
                '_route_params' => ['a route' => 'param', 'and' => 5],
                '_auxmoney_controller' => true
            ]
        );

        $this->tracingId->getAsString()->shouldBeCalledOnce()->willReturn('tracing id');
        $this->tracing->setTagOfActiveSpan('http.status_code', 404)->shouldBeCalledOnce();
        $this->tracing->setTagOfActiveSpan('error', true)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new ResponseEvent($this->kernel->reveal(), $request, 0, new Response('', 404));
        $this->subject->onResponse($event);

        self::assertTrue($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
        self::assertSame('tracing id', $event->getResponse()->headers->get('X-Auxmoney-Opentracing-Trace-Id'));
    }
}
