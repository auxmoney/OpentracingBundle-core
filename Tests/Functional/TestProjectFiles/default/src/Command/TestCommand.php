<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Buzz\Client\Curl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use const OpenTracing\Formats\TEXT_MAP;

class TestCommand extends Command
{
    private $opentracing;
    private $tracing;

    public function __construct(Opentracing $opentracing, Tracing $tracing)
    {
        parent::__construct('test:everything');
        $this->setDescription('some fancy command description');
        $this->opentracing = $opentracing;
        $this->tracing = $tracing;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->tracing->setTagOfActiveSpan('tag.from.command', true);

        $client = new Curl(new Psr17Factory());
        $request = new PSR7Request('GET', 'https://localhost:8000/');
        $response = $client->sendRequest($request, ['timeout' => 10]);
        Assert::eq($response->getStatusCode(), 200);
        Assert::eq(json_decode($response->getBody()->__toString(), true), ['reply' => true]);

        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));
        return 0;
    }
}
