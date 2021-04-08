<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\EventListener;

use Auxmoney\OpentracingBundle\EventListener\StartCommandSpanSubscriber;
use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;

class StartCommandSpanSubscriberTest extends TestCase
{
    private $tracing;
    private $spanOptionsFactory;
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);
        $this->spanOptionsFactory = $this->prophesize(SpanOptionsFactory::class);

        $this->subject = new StartCommandSpanSubscriber($this->tracing->reveal(), $this->spanOptionsFactory->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey('console.command', $this->subject::getSubscribedEvents());
    }

    public function testOnCommand(): void
    {
        $command = $this->prophesize(Command::class);
        $command->getName()->willReturn('command name');
        $command->getDescription()->willReturn('command description');
        $event = $this->prophesize(ConsoleEvent::class);
        $event->getCommand()->willReturn($command->reveal());
        $this->spanOptionsFactory->createSpanOptions()->willReturn(['tags' => ['arbitrary' => 'tag']]);

        $this->tracing->startActiveSpan(
            'command name',
            ['tags' => ['arbitrary' => 'tag', 'command.name' => 'command name', 'command.description' => 'command description', 'span.kind' => 'client', 'auxmoney-opentracing-bundle.span-origin' => 'core:command']]
        )->shouldBeCalledOnce();

        $this->subject->onCommand($event->reveal());
    }

    public function testOnCommandNoName(): void
    {
        $command = $this->prophesize(Command::class);
        $command->getName()->willReturn(null);
        $command->getDescription()->willReturn('command description');
        $event = $this->prophesize(ConsoleEvent::class);
        $event->getCommand()->willReturn($command->reveal());
        $this->spanOptionsFactory->createSpanOptions()->willReturn(['tags' => ['arbitrary' => 'tag']]);

        $this->tracing->startActiveSpan(
            '<unknown>',
            ['tags' => ['arbitrary' => 'tag', 'command.name' => '<unknown>', 'command.description' => 'command description', 'span.kind' => 'client', 'auxmoney-opentracing-bundle.span-origin' => 'core:command']]
        )->shouldBeCalledOnce();

        $this->subject->onCommand($event->reveal());
    }
}
