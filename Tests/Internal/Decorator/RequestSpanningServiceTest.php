<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal\Decorator;

use Auxmoney\OpentracingBundle\Internal\Decorator\RequestSpanningService;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_STATUS_CODE;
use const OpenTracing\Tags\HTTP_URL;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_CLIENT;

class RequestSpanningServiceTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    /** @var RequestSpanningService */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);

        $this->subject = new RequestSpanningService($this->tracing->reveal());
    }

    public function testStart(): void
    {
        $this->tracing->startActiveSpan('sending HTTP request')->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan(SPAN_KIND, SPAN_KIND_RPC_CLIENT)->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan(HTTP_METHOD, 'request Method')->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan(HTTP_URL, 'request URL')->shouldBeCalled();

        $this->subject->start('request Method', 'request URL');
    }

    public function testFinish(): void
    {
        $this->tracing->setTagOfActiveSpan(HTTP_STATUS_CODE, 123)->shouldBeCalled();

        $this->subject->finish(123);
    }

    public function testFinishWith404(): void
    {
        $this->tracing->setTagOfActiveSpan(HTTP_STATUS_CODE, 404)->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan(ERROR, true)->shouldBeCalled();

        $this->subject->finish(404);
    }
}
