<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Symfony\Component\HttpFoundation\Request;

interface SpanOptionsFactory
{
    /**
     * @return array<string,mixed>
     */
    public function createSpanOptions(Request $request = null): array;
}
