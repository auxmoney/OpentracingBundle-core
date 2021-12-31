<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Exception;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;

final class PersistenceService implements Persistence
{
    private Tracer $tracer;
    private LoggerInterface $logger;

    public function __construct(Opentracing $opentracing, LoggerInterface $logger)
    {
        $this->tracer = $opentracing->getTracerInstance();
        $this->logger = $logger;
    }

    public function flush(): void
    {
        try {
            $this->tracer->flush();
        } catch (Exception $exception) {
            $this->logger->warning(self::class . ': Failed to flush tracer : ' . $exception->getMessage());
        }
    }
}
