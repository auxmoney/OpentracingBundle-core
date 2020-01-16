<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Internal\PersistenceService;
use Auxmoney\OpentracingBundle\Tests\Mock\MockTracer;
use PHPUnit\Framework\TestCase;

class PersistenceServiceTest extends TestCase
{
    private $opentracing;

    public function setUp()
    {
        parent::setUp();
        $this->opentracing = $this->prophesize(Opentracing::class);
    }

    public function testFlushSuccess(): void
    {
        $mockTracer = new MockTracer();
        $mockTracer->startActiveSpan('operation name');
        $this->opentracing->getTracerInstance()->willReturn($mockTracer);

        $subject = new PersistenceService($this->opentracing->reveal());

        $subject->flush();

        self::assertEmpty($mockTracer->getSpans());
    }
}
