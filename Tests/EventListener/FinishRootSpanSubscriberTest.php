<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishRootSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class FinishRootSpanSubscriberTest extends TestCase
{
    private $tracing;
    private $persistence;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->persistence = $this->prophesize(Persistence::class);

        $this->subject = new FinishRootSpanSubscriber($this->tracing->reveal(), $this->persistence->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.finish_request', $this->subject::getSubscribedEvents());
    }

    public function testOnFinishRequestIsMasterRequest(): void
    {
        $event = $this->prophesize(KernelEvent::class);
        $event->isMasterRequest()->willReturn(true);

        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();
        $this->persistence->flush()->shouldBeCalledOnce();

        $this->subject->onFinishRequest($event->reveal());
    }

    public function testOnFinishRequestIsNotMasterRequest(): void
    {
        $event = $this->prophesize(KernelEvent::class);
        $event->isMasterRequest()->willReturn(false);

        $this->tracing->finishActiveSpan()->shouldNotBeCalled();
        $this->persistence->flush()->shouldNotBeCalled();

        $this->subject->onFinishRequest($event->reveal());
    }
}
