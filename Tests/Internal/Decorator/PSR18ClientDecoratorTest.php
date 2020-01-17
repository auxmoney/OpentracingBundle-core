<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal\Decorator;

use Auxmoney\OpentracingBundle\Internal\Decorator\PSR18ClientDecorator;
use Auxmoney\OpentracingBundle\Internal\Decorator\RequestSpanning;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class PSR18ClientDecoratorTest extends TestCase
{
    private $decoratedClient;
    private $tracing;
    private $requestSpanning;
    /** @var PSR18ClientDecorator */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->decoratedClient = $this->prophesize(ClientInterface::class);
        $this->tracing = $this->prophesize(Tracing::class);
        $this->requestSpanning = $this->prophesize(RequestSpanning::class);

        $this->subject = new PSR18ClientDecorator(
            $this->decoratedClient->reveal(),
            $this->tracing->reveal(),
            $this->requestSpanning->reveal()
        );
    }

    public function testSendRequestException(): void
    {
        $uri = $this->prophesize(UriInterface::class);
        $uri->__toString()->willReturn('a service uri');
        $request = $this->prophesize(RequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());
        $request->getMethod()->willReturn('HEAD');

        $this->decoratedClient->sendRequest($request->reveal())->willThrow(new RuntimeException('exception happened'));
        $this->requestSpanning->start('HEAD', 'a service uri')->shouldBeCalled();
        $this->requestSpanning->finish(Argument::any())->shouldNotBeCalled();
        $this->tracing->injectTracingHeaders($request->reveal())->willReturn($request->reveal());
        $this->tracing->finishActiveSpan()->shouldBeCalled();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exception happened');

        $this->subject->sendRequest($request->reveal());
    }

    public function testSendRequestSuccess(): void
    {
        $uri = $this->prophesize(UriInterface::class);
        $uri->__toString()->willReturn('a service uri');
        $request = $this->prophesize(RequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());
        $request->getMethod()->willReturn('HEAD');
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(123);

        $this->decoratedClient->sendRequest($request->reveal())->willReturn($response->reveal());
        $this->requestSpanning->start('HEAD', 'a service uri')->shouldBeCalled();
        $this->requestSpanning->finish(123)->shouldBeCalled();
        $this->tracing->injectTracingHeaders($request->reveal())->willReturn($request->reveal());
        $this->tracing->finishActiveSpan()->shouldBeCalled();

        $this->subject->sendRequest($request->reveal());
    }
}
