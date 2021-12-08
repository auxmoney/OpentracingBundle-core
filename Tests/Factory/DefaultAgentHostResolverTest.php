<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\DefaultAgentHostResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DefaultAgentHostResolverTest extends TestCase
{
    /** @var DefaultAgentHostResolver */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new DefaultAgentHostResolver();
    }

    public function testResolveAgentHostSuccessByHost(): void
    {
        $this->subject->ensureAgentHostIsResolvable('localhost');
        self::assertTrue(true); // if no exception is raised, we count this as a successful test
    }

    public function testResolveAgentHostSuccessByIp(): void
    {
        $this->subject->ensureAgentHostIsResolvable('127.0.0.1');
        self::assertTrue(true); // if no exception is raised, we count this as a successful test
    }

    public function testResolveAgentHostFailed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/could not resolve/');

        $this->subject->ensureAgentHostIsResolvable('älsakfdkaofkeäkvaäsooäaegölsgälkfdvpaoskvä.cöm');
    }
}
