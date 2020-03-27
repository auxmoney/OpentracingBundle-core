<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

abstract class JaegerConsoleFunctionalTest extends JaegerWebFunctionalTest
{
    protected function setUpTestProject(string $projectSetup): void
    {
        $this->copyTestProjectFiles($projectSetup);

        $this->composerDumpAutoload();
        $this->consoleCacheClear();
    }

    protected function tearDown()
    {
        $this->gitResetTestProject();
        $this->dockerStopJaeger();
    }
}
