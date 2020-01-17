<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\DependencyInjection;

use Auxmoney\OpentracingBundle\Internal\Decorator\PSR18ClientDecorator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PSR18CompilerPass implements CompilerPassInterface
{
    public const TAG_PSR_18 = 'auxmoney_opentracing.psr_18';

    public function process(ContainerBuilder $container): void
    {
        $psr18Clients = $container->findTaggedServiceIds(self::TAG_PSR_18);
        foreach (array_keys($psr18Clients) as $serviceId) {
            $foo = 'auxmoney_opentracing.decorator.' . $serviceId;
            $container->register($foo, PSR18ClientDecorator::class)
                ->setDecoratedService($serviceId)
                ->setArgument(0, new Reference($foo . '.inner'))
                ->setPublic(false)
                ->setAutowired(true);
        }
    }
}
