<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use function JmesPath\search as jmesSearch;

abstract class JaegerFunctionalTest extends TestCase
{
    protected function setUpTestProject(string $projectSetup): void
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(sprintf('Tests/Functional/TestProjectFiles/%s/', $projectSetup), 'build/testproject/');

        $p = new Process(['composer', 'dump-autoload'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['symfony', 'console', 'cache:clear'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['symfony', 'local:server:start', '-d', '--no-tls'], 'build/testproject');
        $p->mustRun();
    }

    public function setUp()
    {
        parent::setUp();

        $p = new Process(['docker', 'start', 'jaeger']);
        $p->mustRun();

        sleep(3);
    }

    protected function tearDown()
    {
        $p = new Process(['symfony', 'local:server:stop'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['git', 'reset', '--hard', 'reset'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['docker', 'stop', 'jaeger']);
        $p->mustRun();

        parent::tearDown();
    }

    protected function getTraceFromJaegerAPI(string $traceId): array
    {
        $client = new Client();
        $response = $client->get(sprintf('http://localhost:16686/api/traces/%s?raw=true', $traceId));
        return json_decode($response->getBody()->getContents(), true);
    }

    protected function getSpansFromTrace(array $trace): array
    {
        return jmesSearch('data[0].spans', $trace);
    }

    /**
     * @param mixed $spans
     */
    protected function getSpansAsYAML($spans, string $expression): string
    {
        $spanData = jmesSearch(
            $expression,
            $spans
        );

        $nodes = [];
        foreach ($spanData as $data) {
            $node = new stdClass();
            $node->operationName = $data['operationName'];
            if (isset($data['tags'])) {
                $node->tags = $data['tags'];
            }
            if (isset($data['logs'])) {
                $node->logs = $data['logs'];
            }
            $node->childOf = $data['references'][0]['spanID'] ?? null;
            $nodes[$data['spanID']] = $node;
        }

        $rootNode = null;
        foreach ($nodes as $node) {
            if ($node->childOf) {
                $nodes[$node->childOf]->children[] = $node;
            } else {
                $rootNode = $node;
            }
            unset($node->childOf);
        }
        return Yaml::dump($rootNode, 1024, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }
}
