<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\FinishCommandSpanSubscriber;
use Auxmoney\OpentracingBundle\Internal\Persistence;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use const OpenTracing\Tags\ERROR;

class FinishCommandSpanSubscriberTest extends TestCase
{
    use ProphecyTrait;

    private $tracing;
    private $persistence;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->persistence = $this->prophesize(Persistence::class);

        $this->subject = new FinishCommandSpanSubscriber($this->tracing->reveal(), $this->persistence->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('console.terminate', $this->subject::getSubscribedEvents());
    }

    public function testOnTerminate(): void
    {
        $command = $this->prophesize(Command::class);
        $input = $this->prophesize(InputInterface::class);
        $output = $this->prophesize(OutputInterface::class);
        $event = new ConsoleTerminateEvent($command->reveal(), $input->reveal(), $output->reveal(), 253);

        $this->tracing->setTagOfActiveSpan('command.exit-code', 253)->shouldBeCalled();
        $this->tracing->setTagOfActiveSpan(ERROR, true)->shouldBeCalledOnce();
        $this->tracing->finishActiveSpan()->shouldBeCalledOnce();
        $this->persistence->flush()->shouldBeCalledOnce();

        $this->subject->onTerminate($event);
    }
}
