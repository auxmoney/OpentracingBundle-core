<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use OpenTracing\Tracer;

interface TracerFactory
{
    public function create(string $projectName, string $agentHost, string $agentPort): Tracer;
}
