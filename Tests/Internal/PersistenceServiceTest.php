<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Internal\PersistenceService;
use Auxmoney\OpentracingBundle\Tests\Mock\MockTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class PersistenceServiceTest extends TestCase
{
    private $opentracing;

    public function setUp()
    {
        parent::setUp();
        $this->opentracing = $this->prophesize(Opentracing::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testFlushSuccess(): void
    {
        $mockTracer = new MockTracer();
        $mockTracer->startActiveSpan('operation name');
        $this->opentracing->getTracerInstance()->willReturn($mockTracer);
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        $subject = new PersistenceService($this->opentracing->reveal(), $this->logger->reveal());

        $subject->flush();

        self::assertEmpty($mockTracer->getSpans());
    }

    public function testFlushThrowsException(): void
    {
        $mockTracer = $this->prophesize(MockTracer::class);
        $mockTracer->flush()->willThrow(new RuntimeException('exception happened'));
        $mockTracer->reveal()->startActiveSpan('operation name');

        $this->opentracing->getTracerInstance()->willReturn($mockTracer->reveal());
        $this->logger->warning(Argument::type('string'))->shouldBeCalled();

        $subject = new PersistenceService($this->opentracing->reveal(), $this->logger->reveal());

        $subject->flush();
    }
}
