<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingBundle\Factory\TracerFactory;
use Auxmoney\OpentracingBundle\Tests\Mock\MockOpentracingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpentracingExtensionTest extends TestCase
{
    private $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new MockOpentracingExtension();
    }

    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', 'test');
        $container->setParameter('kernel.project_dir', '/some/path/to/random-project');

        $this->subject->load([], $container);

        self::assertSame('unknown', $container->getParameterBag()->all()['env(HOSTNAME)']);
        self::assertSame('random-project', $container->getParameterBag()->all()['env(AUXMONEY_OPENTRACING_PROJECT_NAME)']);
        self::assertSame('mock value', $container->getParameter('mock_parameter'));
        self::assertArrayHasKey('auxmoney_opentracing', $container->getAliases());
        self::assertArrayNotHasKey(TracerFactory::class, $container->getDefinitions());
    }
}
