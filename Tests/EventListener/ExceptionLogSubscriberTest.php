<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\ExceptionLogSubscriber;
use Auxmoney\OpentracingBundle\Tests\Mock\EventReflectionError;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithError;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithException;
use Auxmoney\OpentracingBundle\Tests\Mock\EventWithThrowable;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

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
        $this->tracing->logInActiveSpan(['exception' => 'Exception', 'message' => 'exception'])->shouldBeCalledOnce();

        $this->subject->onException(new EventWithException());
    }

    public function testOnExceptionSuccessThrowable(): void
    {
        $this->tracing->logInActiveSpan(['exception' => 'Exception', 'message' => 'throwable'])->shouldBeCalledOnce();

        $this->subject->onException(new EventWithThrowable());
    }

    public function testOnExceptionSuccessError(): void
    {
        $this->tracing->logInActiveSpan(['exception' => 'Error', 'message' => 'error'])->shouldBeCalledOnce();

        $this->subject->onException(new EventWithError());
    }

    public function testOnExceptionReflectionError(): void
    {
        $this->tracing->logInActiveSpan(['exception' => 'ReflectionException', 'message' => 'this does not work'])->shouldBeCalledOnce();

        $this->subject->onException(new EventReflectionError());
    }

    public function testOnExceptionImproperEvent(): void
    {
        $this->tracing->logInActiveSpan(
            [
                'exception' => 'ReflectionException',
                'message' => 'could not reflect event of type Symfony\Contracts\EventDispatcher\Event'
            ]
        )->shouldBeCalledOnce();

        $this->subject->onException(new Event());
    }
}
