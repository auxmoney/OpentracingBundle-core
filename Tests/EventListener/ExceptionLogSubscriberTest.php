<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\ExceptionLogSubscriber;
use Auxmoney\OpentracingBundle\Tests\Mock\EventReflectionError;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithError;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithException;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithExceptionWithoutErrorMessage;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithThrowable;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;

class ExceptionLogSubscriberTest extends TestCase
{
    private $tracing;
    private $subject;

    public function setUp()
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

    public function testOnExceptionSuccessException(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('error.object', $argument);
            self::assertSame('Exception', $argument['error.object']);
            self::assertArrayHasKey('message', $argument);
            self::assertSame('exception', $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new EventWithException());
    }

    public function testOnExceptionSuccessThrowable(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('error.object', $argument);
            self::assertSame('Exception', $argument['error.object']);
            self::assertArrayHasKey('message', $argument);
            self::assertSame('throwable', $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new EventWithThrowable());
    }

    public function testOnExceptionSuccessError(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('error.object', $argument);
            self::assertSame('Error', $argument['error.object']);
            self::assertArrayHasKey('message', $argument);
            self::assertSame('error', $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new EventWithError());
    }

    public function testOnExceptionReflectionError(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('error.object', $argument);
            self::assertSame('ReflectionException', $argument['error.object']);
            self::assertArrayHasKey('message', $argument);
            self::assertSame('this does not work', $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new EventReflectionError());
    }

    public function testOnExceptionImproperEvent(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('error.object', $argument);
            self::assertSame('ReflectionException', $argument['error.object']);
            self::assertArrayHasKey('message', $argument);
            self::assertSame('could not reflect event of type stdClass', $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new stdClass());
    }

    public function testProvidingDefaultExceptionMessageAsLogsCanNotBeEmpty(): void
    {
        $this->tracing->logInActiveSpan(Argument::that(function (array $argument) {
            self::assertArrayHasKey('message', $argument);
            self::assertEquals("No error message given", $argument['message']);
            return true;
        }))->shouldBeCalledOnce();

        $this->subject->onException(new EventWithExceptionWithoutErrorMessage());
    }
}
