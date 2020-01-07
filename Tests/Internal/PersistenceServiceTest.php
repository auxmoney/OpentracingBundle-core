<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Internal\PersistenceService;
use Auxmoney\OpentracingBundle\Mock\MockTracer;
use PHPUnit\Framework\TestCase;

class PersistenceServiceTest extends TestCase
{
    private $opentracing;
    /** @var PersistenceService */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->opentracing = $this->prophesize(Opentracing::class);

        $this->subject = new PersistenceService($this->opentracing->reveal());
    }

    public function testFlushSuccess(): void
    {
        $mockTracer = new MockTracer();
        $mockTracer->startActiveSpan('operation name');
        $this->opentracing->getTracerInstance()->willReturn($mockTracer);

        $this->subject->flush();

        self::assertEmpty($mockTracer->getSpans());
    }
}
