<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Auxmoney\OpentracingBundle\Factory\TracerFactory;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;

final class CachedOpentracing implements Opentracing
{
    private ?Tracer $tracerInstance = null;
    private TracerFactory $tracerFactory;
    private LoggerInterface $logger;
    private string $projectName;
    private string $agentHost;
    private string $agentPort;
    private string $samplerClass;

    /** @var mixed */
    private $samplerValue;

    /**
     * @param mixed $samplerValue
     */
    public function __construct(
        TracerFactory $tracerFactory,
        LoggerInterface $logger,
        string $projectName,
        string $agentHost,
        string $agentPort,
        string $samplerClass,
        $samplerValue
    ) {
        $this->tracerFactory = $tracerFactory;
        $this->logger = $logger;
        $this->projectName = $projectName;
        $this->agentHost = $agentHost;
        $this->agentPort = $agentPort;
        $this->samplerClass = $samplerClass;
        $this->samplerValue = $samplerValue;
    }

    public function getTracerInstance(): Tracer
    {
        if (!$this->tracerInstance) {
            $this->tracerInstance = $this->tracerFactory->create(
                $this->projectName,
                $this->agentHost,
                $this->agentPort,
                $this->samplerClass,
                $this->samplerValue
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
