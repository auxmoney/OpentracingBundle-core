<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\EventListener;

use Auxmoney\OpentracingBundle\Factory\SpanOptionsFactory;
use Auxmoney\OpentracingBundle\Internal\Constant;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_CLIENT;

final class StartCommandSpanSubscriber implements EventSubscriberInterface
{
    private $tracing;
    private $spanOptionsFactory;

    public function __construct(Tracing $tracing, SpanOptionsFactory $spanOptionsFactory)
    {
        $this->tracing = $tracing;
        $this->spanOptionsFactory = $spanOptionsFactory;
    }

    /**
     * @return array<string,array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'console.command' => ['onCommand', 4096],
        ];
    }

    public function onCommand(ConsoleEvent $event): void
    {
        /** @var Command $command */
        $command = $event->getCommand();
        $commandName = $command->getName() ?? '<unknown>';

        $options = $this->spanOptionsFactory->createSpanOptions();
        $options['tags']['command.name'] = $commandName;
        $options['tags']['command.description'] = $command->getDescription();
        $options['tags'][SPAN_KIND] = SPAN_KIND_RPC_CLIENT;
        $options['tags'][Constant::SPAN_ORIGIN] = 'core:command';

        $this->tracing->startActiveSpan(
            $commandName,
            $options
        );
    }
}
