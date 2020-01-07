<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Factory\TracerFactory;
use Auxmoney\OpentracingBundle\Internal\CachedOpentracing;
use Auxmoney\OpentracingBundle\Mock\MockTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class CachedOpentracingTest extends TestCase
{
    private $logger;
    private $projectName;
    private $agentHost;
    private $agentPort;
    private $tracerFactory;
    /** @var CachedOpentracing */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->tracerFactory = $this->prophesize(TracerFactory::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->projectName = 'project name';
        $this->agentHost = 'agent host';
        $this->agentPort = '1234';

        $this->subject = new CachedOpentracing(
            $this->tracerFactory->reveal(),
            $this->logger->reveal(),
            $this->projectName,
            $this->agentHost,
            $this->agentPort
        );
    }

    public function testGetTracerInstanceSuccess(): void
    {
        $this->logger->debug(Argument::any())->shouldBeCalledOnce();

        $this->tracerFactory->create($this->projectName, $this->agentHost, $this->agentPort)->willReturn(new MockTracer());

        $tracer1 = $this->subject->getTracerInstance();
        $tracer2 = $this->subject->getTracerInstance();

        self::assertSame($tracer1, $tracer2);
    }
}
