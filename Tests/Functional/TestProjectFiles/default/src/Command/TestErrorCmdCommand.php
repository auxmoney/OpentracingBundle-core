<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use const OpenTracing\Formats\TEXT_MAP;

class TestErrorCmdCommand extends Command
{
    private $opentracing;

    public function __construct(Opentracing $opentracing)
    {
        parent::__construct('test:error:cmd');
        $this->setDescription('some fancy command description');
        $this->opentracing = $opentracing;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));

        throw new Exception('unhandled command exception');
    }
}
