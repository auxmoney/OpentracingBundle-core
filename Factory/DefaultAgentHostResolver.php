<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use RuntimeException;

final class DefaultAgentHostResolver implements AgentHostResolver
{
    public function ensureAgentHostIsResolvable(string $agentHost): void
    {
        if (gethostbyname($agentHost) === $agentHost && !filter_var($agentHost, FILTER_VALIDATE_IP)) {
            throw new RuntimeException(self::class . ': could not resolve agent host "' . $agentHost . '"');
        }
    }
}
