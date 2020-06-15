<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal\Decorator;

use Auxmoney\OpentracingBundle\Internal\Constant;
use Auxmoney\OpentracingBundle\Service\Tracing;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class PSR18ClientDecorator implements ClientInterface
{
    private $decoratedClient;
    private $tracing;
    private $requestSpanning;

    public function __construct(ClientInterface $decoratedClient, Tracing $tracing, RequestSpanning $requestSpanning)
    {
        $this->decoratedClient = $decoratedClient;
        $this->tracing = $tracing;
        $this->requestSpanning = $requestSpanning;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requestSpanning->start($request->getMethod(), $request->getUri()->__toString());

        try {
            $this->tracing->setTagOfActiveSpan(Constant::SPAN_ORIGIN, 'core:psr-18');

            $request = $this->tracing->injectTracingHeaders($request);

            $response = $this->decoratedClient->sendRequest($request);

            $this->requestSpanning->finish($response->getStatusCode());

            return $response;
        } finally {
            $this->tracing->finishActiveSpan();
        }
    }
}
