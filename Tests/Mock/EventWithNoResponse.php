<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Symfony\Component\HttpFoundation\Request;

final class EventWithNoResponse
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
}
