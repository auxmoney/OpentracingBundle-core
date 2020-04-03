<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use const OpenTracing\Formats\TEXT_MAP;

class TestCommand extends Command
{
    private $opentracing;
    private $tracing;
    private $client;
    private $requestFactory;

    public function __construct(Opentracing $opentracing, Tracing $tracing, ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        parent::__construct('test:all-features');
        $this->setDescription('some fancy command description');
        $this->opentracing = $opentracing;
        $this->tracing = $tracing;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->tracing->setTagOfActiveSpan('tag.from.command', true);

        $request = $this->requestFactory->createRequest('GET', 'http://localhost:8000/');
        $response = $this->client->sendRequest($request);
        Assert::eq($response->getStatusCode(), 200);
        Assert::eq(json_decode($response->getBody()->__toString(), true), ['reply' => true]);

        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));
        return 0;
    }
}
