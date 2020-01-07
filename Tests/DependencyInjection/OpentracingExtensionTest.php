<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingBundle\DependencyInjection\OpentracingExtension;
use Auxmoney\OpentracingBundle\Factory\TracerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class OpentracingExtensionTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new OpentracingExtension();
    }

    public function testLoadDefault(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', 'test');
        $container->setParameter('kernel.project_dir', '/some/path/to/random-project');

        $this->subject->load([], $container);

        self::assertSame('unknown', $container->getParameterBag()->all()['env(HOSTNAME)']);
        self::assertSame('random-project', $container->getParameterBag()->all()['env(AUXMONEY_OPENTRACING_PROJECT_NAME)']);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessageRegExp('/You have requested a non-existent service/');
        $container->getDefinition(TracerFactory::class);
    }
}
