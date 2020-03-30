<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

use Symfony\Component\Process\Process;

class FunctionalTest extends JaegerWebFunctionalTest
{
    /**
     * tested features:
     *      - command spanning
     *      - request spanning
     *      - psr-18 propagation
     *      - tagging
     *      - logging
     */
    public function testAllFeatures(): void
    {
        $this->setUpTestProject('default');

        $process = new Process(['symfony', 'console', 'test:everything'], 'build/testproject');
        $process->mustRun();
        $output = $process->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(4, $spans);

        $traceAsYAML = $this->getSpansAsYAML($spans, '[].{operationName: operationName, startTime: startTime, spanID: spanID, references: references, logs: logs[].{fields: fields}, tags: tags[?key==\'http.status_code\' || key==\'command.exit-code\' || key==\'http.url\' || key==\'http.method\'].{key: key, value: value}}');
        self::assertStringEqualsFile(__DIR__ . '/FunctionalTest.allFeatures.expected.yaml', $traceAsYAML);
    }

    public function testWebException(): void
    {
        $this->setUpTestProject('default');

        $process = new Process(['symfony', 'console', 'test:error:web'], 'build/testproject');
        $process->mustRun();
        $output = $process->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(4, $spans);

        $traceAsYAML = $this->getSpansAsYAML($spans, '[].{operationName: operationName, startTime: startTime, spanID: spanID, references: references, logs: logs[].{fields: fields[?key==\'error.kind\' || key==\'event\' || key==\'error.object\' || key==\'message\']}, tags: tags[?key==\'http.status_code\' || key==\'command.exit-code\' || key==\'http.url\' || key==\'http.method\'].{key: key, value: value}}');
        self::assertStringEqualsFile(__DIR__ . '/FunctionalTest.webException.expected.yaml', $traceAsYAML);
    }

    public function testCommandException(): void
    {
        $this->setUpTestProject('default');

        $process = new Process(['symfony', 'console', 'test:error:cmd'], 'build/testproject');
        $exitCode = $process->run();
        self::assertSame(1, $exitCode);
        self::assertStringContainsString('unhandled command exception', $process->getErrorOutput());

        $output = $process->getOutput();
        $traceId = substr($output, 0, strpos($output, ':'));
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(1, $spans);

        $traceAsYAML = $this->getSpansAsYAML($spans, '[].{operationName: operationName, startTime: startTime, spanID: spanID, references: references, logs: logs[].{fields: fields[?key==\'error.kind\' || key==\'event\' || key==\'error.object\' || key==\'message\']}, tags: tags[?key==\'http.status_code\' || key==\'command.exit-code\' || key==\'http.url\' || key==\'http.method\'].{key: key, value: value}}');
        self::assertStringEqualsFile(__DIR__ . '/FunctionalTest.cmdException.expected.yaml', $traceAsYAML);
    }
}
