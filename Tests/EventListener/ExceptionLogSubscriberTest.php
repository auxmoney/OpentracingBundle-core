<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\ExceptionLogSubscriber;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionLogSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private ExceptionLogSubscriber $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);

        $this->subject = new ExceptionLogSubscriber($this->tracing->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subject::getSubscribedEvents();
        self::assertArrayHasKey('kernel.exception', $subscribedEvents);
        self::assertArrayHasKey('console.error', $subscribedEvents);
    }

    public function testOnExceptionSuccessThrowable(): void
    {
        $this->tracing->logInActiveSpan(
            Argument::that(function (array $argument) {
                self::assertArrayHasKey('error.object', $argument);
                self::assertSame('Exception', $argument['error.object']);
                self::assertArrayHasKey('message', $argument);
                self::assertSame('throwable', $argument['message']);
                return true;
            })
        )->shouldBeCalledOnce();

        $this->subject->onException(
            new ExceptionEvent(
                $this->prophesize(HttpKernelInterface::class)->reveal(),
                $this->prophesize(Request::class)->reveal(),
                0,
                new Exception('throwable')
            )
        );
    }

    public function testOnExceptionSuccessError(): void
    {
        $this->tracing->logInActiveSpan(
            Argument::that(function (array $argument) {
                self::assertArrayHasKey('error.object', $argument);
                self::assertSame('Error', $argument['error.object']);
                self::assertArrayHasKey('message', $argument);
                self::assertSame('error', $argument['message']);
                return true;
            })
        )->shouldBeCalledOnce();

        $this->subject->onException(
            new ExceptionEvent(
                $this->prophesize(HttpKernelInterface::class)->reveal(),
                $this->prophesize(Request::class)->reveal(),
                0,
                new Error('error')
            )
        );
    }

    public function testProvidingDefaultExceptionMessageAsLogsCanNotBeEmpty(): void
    {
        $this->tracing->logInActiveSpan(
            Argument::that(function (array $argument) {
                self::assertArrayHasKey('message', $argument);
                self::assertEquals("No error message given", $argument['message']);
                return true;
            })
        )->shouldBeCalledOnce();

        $this->subject->onException(
            new ExceptionEvent(
                $this->prophesize(HttpKernelInterface::class)->reveal(),
                $this->prophesize(Request::class)->reveal(),
                0,
                new Error()
            )
        );
    }
}
