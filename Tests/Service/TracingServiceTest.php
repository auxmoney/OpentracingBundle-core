<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Service;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Tests\Mock\MockTracer;
use Auxmoney\OpentracingBundle\Service\TracingService;
use OpenTracing\Mock\MockSpan;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class TracingServiceTest extends TestCase
{
    private $opentracing;
    private $logger;
    private $mockTracer;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockTracer = new MockTracer();
        $this->opentracing = $this->prophesize(Opentracing::class);
        $this->opentracing->getTracerInstance()->willReturn($this->mockTracer);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->subject = new TracingService($this->opentracing->reveal(), $this->logger->reveal());
    }

    public function testInjectTracingHeadersSuccess(): void
    {
        $this->mockTracer->startActiveSpan('test span');

        $originalRequest = $this->prophesize(RequestInterface::class);
        $newRequest = $this->prophesize(RequestInterface::class);

        $originalRequest->withHeader('made_up_header', '1:2:3:4')->shouldBeCalled()->willReturn($newRequest->reveal());
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        $injectedRequest = $this->subject->injectTracingHeaders($originalRequest->reveal());
        self::assertSame($newRequest->reveal(), $injectedRequest);
    }

    public function testInjectTracingHeadersNoActiveSpan(): void
    {
        $originalRequest = $this->prophesize(RequestInterface::class);

        $originalRequest->withHeader('made_up_header', '1:2:3:4')->shouldNotBeCalled();
        $this->logger->warning(Argument::type('string'))->shouldBeCalled();

        $injectedRequest = $this->subject->injectTracingHeaders($originalRequest->reveal());
        self::assertSame($originalRequest->reveal(), $injectedRequest);
    }

    public function testStartActiveSpanWithoutOptionsSuccess(): void
    {
        $this->subject->startActiveSpan('operation name');

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNotNull($activeSpan);
        self::assertSame('operation name', $activeSpan->getOperationName());
        self::assertSame([], $activeSpan->getTags());
    }

    public function testStartActiveSpanWithOptionsSuccess(): void
    {
        $this->subject->startActiveSpan('operation name', ['tags' => ['a' => 'tag']]);

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNotNull($activeSpan);
        self::assertSame('operation name', $activeSpan->getOperationName());
        self::assertSame(['a' => 'tag'], $activeSpan->getTags());
    }

    public function testLogInActiveSpanSuccess(): void
    {
        $this->subject->startActiveSpan('operation name');

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        $this->subject->logInActiveSpan(['field 1' => 'value 1', 'field 2' => 'value 2']);

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNotNull($activeSpan);
        self::assertSame('operation name', $activeSpan->getOperationName());
        self::assertCount(1, $activeSpan->getLogs());
        self::assertSame(['field 1' => 'value 1', 'field 2' => 'value 2'], $activeSpan->getLogs()[0]['fields']);
    }

    public function testLogInActiveSpanNoActiveSpan(): void
    {
        $this->logger->warning(Argument::type('string'))->shouldBeCalled();

        $this->subject->logInActiveSpan(['field 1' => 'value 1', 'field 2' => 'value 2']);

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNull($activeSpan);
    }

    public function testSetTagOfActiveSpanSuccess(): void
    {
        $this->subject->startActiveSpan('operation name');

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        $this->subject->setTagOfActiveSpan('tag 1', 'value 1');

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNotNull($activeSpan);
        self::assertSame('operation name', $activeSpan->getOperationName());
        self::assertCount(1, $activeSpan->getTags());
        self::assertArrayHasKey('tag 1', $activeSpan->getTags());
        self::assertSame('value 1', $activeSpan->getTags()['tag 1']);
    }

    public function testSetTagOfActiveSpanNoActiveSpan(): void
    {
        $this->logger->warning(Argument::type('string'))->shouldBeCalled();

        $this->subject->setTagOfActiveSpan('tag 1', 'value 1');

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNull($activeSpan);
    }

    public function testFinishActiveSpanSuccess(): void
    {
        $this->subject->startActiveSpan('operation name');

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        $this->subject->finishActiveSpan();

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNull($activeSpan);
    }

    public function testFinishActiveSpanNoActiveSpan(): void
    {
        $this->logger->warning(Argument::type('string'))->shouldBeCalled();

        $this->subject->finishActiveSpan();

        /** @var MockSpan $activeSpan */
        $activeSpan = $this->mockTracer->getActiveSpan();
        self::assertNull($activeSpan);
    }
}
