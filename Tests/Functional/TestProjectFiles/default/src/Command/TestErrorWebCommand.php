<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\Opentracing;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use const OpenTracing\Formats\TEXT_MAP;

class TestErrorWebCommand extends Command
{
    private $opentracing;
    private $client;
    private $requestFactory;

    public function __construct(Opentracing $opentracing, ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        parent::__construct('test:error:web');
        $this->setDescription('some fancy command description');
        $this->opentracing = $opentracing;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = $this->requestFactory->createRequest('GET', 'http://localhost:8000/error');
        $response = $this->client->sendRequest($request);
        Assert::eq($response->getStatusCode(), 500);

        $carrier = [];
        $this->opentracing->getTracerInstance()->inject($this->opentracing->getTracerInstance()->getActiveSpan()->getContext(), TEXT_MAP, $carrier);
        $output->writeln(current($carrier));
        return 0;
    }
}
