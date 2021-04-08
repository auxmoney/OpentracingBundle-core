<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\RequestSpanOptionsFactory;
use Auxmoney\OpentracingBundle\Internal\Utility;
use OpenTracing\NoopSpanContext;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class RequestSpanOptionsFactoryTest extends TestCase
{
    private $utility;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->utility = $this->prophesize(Utility::class);
        $kernelDebug = 'false';
        $kernelEnvironment = 'some environment';
        $hostName = 'hostname';

        $this->subject = new RequestSpanOptionsFactory(
            $this->utility->reveal(), $kernelDebug, $kernelEnvironment, $hostName
        );
    }

    public function testCreateSpanOptionsWithoutRequest(): void
    {
        $this->utility->extractSpanContext(Argument::any())->shouldNotBeCalled();

        $options = $this->subject->createSpanOptions();

        self::assertIsArray($options);
        self::assertArrayHasKey('tags', $options);
        self::assertArrayHasKey('kernel.environment', $options['tags']);
        self::assertSame('some environment', $options['tags']['kernel.environment']);
        self::assertArrayNotHasKey('child_of', $options);
    }

    public function testCreateSpanOptionsWithoutParentContext(): void
    {
        $request = new Request();
        $request->headers->set('header', 'value');

        $this->utility->extractSpanContext(['header' => ['value']])->willReturn(null);

        $options = $this->subject->createSpanOptions($request);

        self::assertIsArray($options);
        self::assertArrayHasKey('tags', $options);
        self::assertArrayHasKey('kernel.environment', $options['tags']);
        self::assertSame('some environment', $options['tags']['kernel.environment']);
        self::assertArrayNotHasKey('child_of', $options);
    }

    public function testCreateSpanOptionsWithParentContext(): void
    {
        $spanContext = new NoopSpanContext();
        $request = new Request();
        $request->headers->set('header', 'value');

        $this->utility->extractSpanContext(['header' => ['value']])->willReturn($spanContext);

        $options = $this->subject->createSpanOptions($request);

        self::assertIsArray($options);
        self::assertArrayHasKey('tags', $options);
        self::assertArrayHasKey('kernel.environment', $options['tags']);
        self::assertSame('some environment', $options['tags']['kernel.environment']);
        self::assertArrayHasKey('child_of', $options);
        self::assertSame($spanContext, $options['child_of']);
    }
}
