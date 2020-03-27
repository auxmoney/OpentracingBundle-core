<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

use Symfony\Component\Process\Process;

class FunctionalTest extends JaegerWebFunctionalTest
{
    public function testAllFeatures(): void
    {
        $this->setUpTestProject('default');

        $process = new Process(['symfony', 'console', 'test:everything'], 'build/testproject');
        $process->mustRun();
        $output = $process->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(100, $spans);
        self::assertSame('test:everything', $spans[0]['operationName']);
    }
}
