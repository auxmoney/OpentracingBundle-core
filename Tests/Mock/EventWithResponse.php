<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

final class EventWithResponse
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return new Response();
    }
}
