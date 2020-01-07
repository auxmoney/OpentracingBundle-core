<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Internal\UtilityService;
use Auxmoney\OpentracingBundle\Tests\Mock\MockTracer;
use OpenTracing\Tracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use const OpenTracing\Formats\TEXT_MAP;

class UtilityServiceTest extends TestCase
{
    private $opentracing;
    /** @var UtilityService */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->opentracing = $this->prophesize(Opentracing::class);

        $this->subject = new UtilityService($this->opentracing->reveal());
    }

    public function testExtractSpanContextNoSpanContext(): void
    {
        $tracer = $this->prophesize(Tracer::class);
        $tracer->extract(TEXT_MAP, Argument::any())->willReturn(null);
        $this->opentracing->getTracerInstance()->willReturn($tracer->reveal());

        $extractedSpanContext = $this->subject->extractSpanContext(['input' => 'headers']);
        self::assertNull($extractedSpanContext);
    }

    public function testExtractSpanContextSuccess(): void
    {
        $this->opentracing->getTracerInstance()->willReturn(new MockTracer());

        $extractedSpanContext = $this->subject->extractSpanContext(['input' => 'headers']);
        self::assertNotNull($extractedSpanContext);
    }
}
