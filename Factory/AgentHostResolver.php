<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use RuntimeException;

interface AgentHostResolver
{
    /**
     * @throws RuntimeException
     */
    public function ensureAgentHostIsResolvable(string $agentHost): void;
}
