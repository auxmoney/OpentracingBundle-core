<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class OpentracingExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     * @throws Exception
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadBundleServices($container);
        $this->overwriteProjectNameParameter($container);
    }

    /**
     * @throws Exception
     */
    private function loadBundleServices(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }

    private function overwriteProjectNameParameter(ContainerBuilder $container): void
    {
        $projectDirectory = $container->getParameter('kernel.project_dir');
        $container->setParameter('env(AUXMONEY_OPENTRACING_PROJECT_NAME)', basename($projectDirectory));
    }
}
