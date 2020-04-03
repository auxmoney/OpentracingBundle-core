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

abstract class JaegerWebFunctionalTest extends TestCase
{
    protected const BUILD_TESTPROJECT = 'build/testproject';

    protected function setUpTestProject(string $projectSetup): void
    {
        $this->copyTestProjectFiles($projectSetup);

        $this->composerDumpAutoload();
        $this->consoleCacheClear();
        $this->symfonyLocalServerStart();
    }

    public function setUp()
    {
        $this->dockerStartJaeger();
    }

    protected function tearDown()
    {
        $this->symfonyLocalServerStop();
        $this->gitResetTestProject();
        $this->dockerStopJaeger();
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

        $nodes = $this->transformSpanDataToNodes($spanData);
        $rootNode = $this->buildTree($nodes);
        $this->sortNodesByStartTime($nodes);

        return Yaml::dump($rootNode, 1024, 2, Yaml::DUMP_OBJECT_AS_MAP);
    }

    private function transformSpanDataToNodes(array $spanData): array
    {
        $nodes = [];
        foreach ($spanData as $data) {
            $node = new stdClass();
            $node->operationName = $data['operationName'];
            $node->startTime = $data['startTime'];
            if (isset($data['tags'])) {
                $node->tags = $data['tags'];
            }
            if (isset($data['logs'])) {
                $node->logs = $data['logs'];
            }
            $node->childOf = $data['references'][0]['spanID'] ?? null;
            $nodes[$data['spanID']] = $node;
        }
        return $nodes;
    }

    private function buildTree(array &$nodes)
    {
        $rootNode = null;
        foreach ($nodes as $node) {
            if ($node->childOf) {
                $nodes[$node->childOf]->children[] = $node;
            } else {
                $rootNode = $node;
            }
            unset($node->childOf);
        }
        return $rootNode;
    }

    private function sortNodesByStartTime(array &$nodes): void
    {
        foreach ($nodes as $node) {
            if (!empty($node->children)) {
                usort(
                    $node->children,
                    static function ($nodeA, $nodeB) {
                        return $nodeA->startTime - $nodeB->startTime;
                    }
                );
            }
        }
        foreach ($nodes as $node) {
            unset($node->startTime);
        }
    }

    protected function runInTestProject(array $commandLine): void
    {
        $process = new Process($commandLine, self::BUILD_TESTPROJECT);
        $process->mustRun();
    }

    protected function composerDumpAutoload(): void
    {
        $this->runInTestProject(['composer', 'dump-autoload']);
    }

    protected function consoleCacheClear(): void
    {
        $this->runInTestProject(['symfony', 'console', 'cache:clear']);
    }

    protected function gitResetTestProject(): void
    {
        $this->runInTestProject(['git', 'reset', '--hard', 'reset']);
    }

    protected function copyTestProjectFiles(string $projectSetup): void
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(sprintf('Tests/Functional/TestProjectFiles/%s/', $projectSetup), self::BUILD_TESTPROJECT . '/', null, ['override' => true]);
    }

    protected function symfonyLocalServerStart(): void
    {
        $this->runInTestProject(['symfony', 'local:server:start', '-d', '--no-tls']);
    }

    protected function symfonyLocalServerStop(): void
    {
        $this->runInTestProject(['symfony', 'local:server:stop']);
    }

    protected function dockerStartJaeger(): void
    {
        $this->runInTestProject(['docker', 'start', 'jaeger']);
        sleep(3);
    }

    protected function dockerStopJaeger(): void
    {
        $this->runInTestProject(['docker', 'stop', 'jaeger']);
    }
}
