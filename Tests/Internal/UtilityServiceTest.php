<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Internal\UtilityService;
use Auxmoney\OpentracingBundle\Tests\Mock\MockTracer;
use OpenTracing\Tracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use const OpenTracing\Formats\TEXT_MAP;

class UtilityServiceTest extends TestCase
{
    use ProphecyTrait;

    private $opentracing;

    public function setUp(): void
    {
        parent::setUp();
        $this->opentracing = $this->prophesize(Opentracing::class);
    }

    public function testExtractSpanContextNoSpanContext(): void
    {
        $tracer = $this->prophesize(Tracer::class);
        $tracer->extract(TEXT_MAP, Argument::any())->willReturn(null);
        $this->opentracing->getTracerInstance()->willReturn($tracer->reveal());

        $subject = new UtilityService($this->opentracing->reveal());

        $extractedSpanContext = $subject->extractSpanContext(['input' => 'headers']);
        self::assertNull($extractedSpanContext);
    }

    public function testExtractSpanContextSuccess(): void
    {
        $this->opentracing->getTracerInstance()->willReturn(new MockTracer());

        $subject = new UtilityService($this->opentracing->reveal());

        $extractedSpanContext = $subject->extractSpanContext(['input' => 'headers']);
        self::assertNotNull($extractedSpanContext);
    }
}
