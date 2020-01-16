<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Mock;

use Auxmoney\OpentracingBundle\DependencyInjection\AbstractOpentracingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MockOpentracingExtension extends AbstractOpentracingExtension
{
    protected function loadBundleServices(ContainerBuilder $container): void
    {
        $container->setParameter('mock_parameter', 'mock value');
    }
}
