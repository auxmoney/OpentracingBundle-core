<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Psr\Log\LoggerInterface;

final class PersistenceService implements Persistence
{
    private $tracer;
    private $logger;

    public function __construct(Opentracing $opentracing, LoggerInterface $logger)
    {
        $this->tracer = $opentracing->getTracerInstance();
        $this->logger = $logger;
    }

    public function flush(): void
    {
        try {
            $this->tracer->flush();
        } catch (\Throwable $exception) {
            $this->logger->warning('Failed to flush tracer : ' . $exception->getMessage());
        }
    }
}
