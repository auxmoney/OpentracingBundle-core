<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishControllerSpanSubscriber;
use Auxmoney\OpentracingBundle\Mock\EventWithResponse;
use Auxmoney\OpentracingBundle\Mock\EventWithResponseAndReflectionError;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status-code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new GenericEvent());
    }

    public function testOnTerminateReflectionFailed(): void
    {
        $this->logger->error(Argument::any())->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status-code', 'not determined')->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithResponseAndReflectionError());
    }

    public function testOnTerminateSuccess(): void
    {
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->tracing->setTagOfActiveSpan('http.status-code', 200)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();

        $this->subject->onResponse(new EventWithResponse());
    }
}
