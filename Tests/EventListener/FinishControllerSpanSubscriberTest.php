<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishControllerSpanSubscriber;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithNoResponse;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithResponse;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithResponseAndReflectionError;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class FinishControllerSpanSubscriberTest extends TestCase
{
    private $logger;
    private $tracing;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->subject = new FinishControllerSpanSubscriber($this->tracing->reveal(), $this->logger->reveal());
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
        $this->tracing->setTagOfActiveSpan('http.status_code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithNoResponse($request));
    }

    public function testOnTerminateNoController(): void
    {
        $request = new Request();

        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->tracing->finishActiveSpan()->shouldNotBeCalled();

        $this->subject->onResponse(new EventWithResponse($request));
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
        $this->tracing->setTagOfActiveSpan('http.status_code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithResponseAndReflectionError($request));
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
        $this->tracing->setTagOfActiveSpan('http.status_code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithResponse($request));
    }
}
