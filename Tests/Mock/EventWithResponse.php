<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Symfony\Component\HttpFoundation\Response;

final class EventWithResponse
{
    public function getResponse(): Response
    {
        return new Response();
    }
}
