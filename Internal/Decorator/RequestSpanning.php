<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal\Decorator;

interface RequestSpanning
{
    public function start(string $requestMethod, string $requestUrl): void;

    public function finish(int $responseStatusCode): void;
}
