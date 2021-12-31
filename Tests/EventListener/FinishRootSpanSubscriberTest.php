<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishRootSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FinishRootSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private $persistence;
    private $kernel;
    private $request;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->persistence = $this->prophesize(Persistence::class);
        $this->kernel = $this->prophesize(HttpKernelInterface::class);
        $this->request = $this->prophesize(Request::class);

        $this->subject = new FinishRootSpanSubscriber($this->tracing->reveal(), $this->persistence->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('kernel.finish_request', $this->subject::getSubscribedEvents());
    }

    public function testOnFinishRequestIsMainRequest(): void
    {
        $event = new KernelEvent($this->kernel->reveal(), $this->request->reveal(), HttpKernelInterface::MASTER_REQUEST);

        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();
        $this->persistence->flush()->shouldBeCalledOnce();

        $this->subject->onFinishRequest($event);
    }

    public function testOnFinishRequestIsNotMainRequest(): void
    {
        $event = new KernelEvent($this->kernel->reveal(), $this->request->reveal(), HttpKernelInterface::SUB_REQUEST);

        $this->tracing->finishActiveSpan()->shouldNotBeCalled();
        $this->persistence->flush()->shouldNotBeCalled();

        $this->subject->onFinishRequest($event);
    }
}
