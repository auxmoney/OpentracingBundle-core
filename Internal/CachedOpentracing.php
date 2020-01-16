<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Auxmoney\OpentracingBundle\Factory\TracerFactory;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;

final class CachedOpentracing implements Opentracing
{
    /** @var ?Tracer */
    private $tracerInstance;
    private $tracerFactory;
    private $projectName;
    private $agentHost;
    private $agentPort;
    private $logger;

    public function __construct(
        TracerFactory $tracerFactory,
        LoggerInterface $logger,
        string $projectName,
        string $agentHost,
        string $agentPort
    ) {
        $this->tracerFactory = $tracerFactory;
        $this->logger = $logger;
        $this->projectName = $projectName;
        $this->agentHost = $agentHost;
        $this->agentPort = $agentPort;
    }

    public function getTracerInstance(): Tracer
    {
        if (!$this->tracerInstance) {
            $this->tracerInstance = $this->tracerFactory->create(
                $this->projectName,
                $this->agentHost,
                $this->agentPort
            );

            $this->logger->debug(
                sprintf(
                    'created a %s named "%s" and connecting to %s:%s',
                    get_class($this->tracerInstance),
                    $this->projectName,
                    $this->agentHost,
                    $this->agentPort
                )
            );
        }

        return $this->tracerInstance;
    }
}
