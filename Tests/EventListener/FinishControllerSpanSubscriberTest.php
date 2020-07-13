<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishControllerSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\TracingId;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithNoResponse;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithResponse;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithResponseAndReflectionError;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class FinishControllerSpanSubscriberTest extends TestCase
{
    private $tracingId;
    private $logger;
    private $tracing;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->tracingId = $this->prophesize(TracingId::class);

        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            $this->logger->reveal(),
            'true'
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.response', $this->subject::getSubscribedEvents());
    }

    public function testOnTerminateNoResponse(): void
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

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status_code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithNoResponse($request));
    }

    public function testOnTerminateNoController(): void
    {
        $request = new Request();

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->tracing->finishActiveSpan()->shouldNotBeCalled();

        $event = new EventWithResponse($request);
        $this->subject->onResponse($event);

        self::assertFalse($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
    }

    public function testOnTerminateReflectionFailed(): void
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

        $this->logger->error(Argument::any())->shouldBeCalled();
        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status_code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithResponseAndReflectionError($request));
    }

    public function testOnTerminateSuccessWithoutTraceId(): void
    {
        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            $this->logger->reveal(),
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

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracingId->getAsString()->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status_code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new EventWithResponse($request);
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

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracingId->getAsString()->shouldBeCalledOnce()->willReturn('tracing id');
        $this->tracing->setTagOfActiveSpan('http.status_code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new EventWithResponse($request);
        $this->subject->onResponse($event);

        self::assertTrue($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
        self::assertSame('tracing id', $event->getResponse()->headers->get('X-Auxmoney-Opentracing-Trace-Id'));
    }

    public function testOnTerminateSuccessWith404(): void
    {
        $this->subject = new FinishControllerSpanSubscriber(
            $this->tracing->reveal(),
            $this->tracingId->reveal(),
            $this->logger->reveal(),
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

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracingId->getAsString()->shouldBeCalledOnce()->willReturn('tracing id');
        $this->tracing->setTagOfActiveSpan('http.status_code', 404)->shouldBeCalledOnce();
        $this->tracing->setTagOfActiveSpan('error', true)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $event = new EventWithResponse($request);
        $event->getResponse()->setStatusCode(404);
        $this->subject->onResponse($event);

        self::assertTrue($event->getResponse()->headers->has('X-Auxmoney-Opentracing-Trace-Id'));
        self::assertSame('tracing id', $event->getResponse()->headers->get('X-Auxmoney-Opentracing-Trace-Id'));
    }
}
