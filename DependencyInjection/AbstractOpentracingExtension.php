<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class AbstractOpentracingExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     * @throws Exception

     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadBundleServices($container);
        $this->loadCoreServices($container);
        $this->overwriteProjectNameParameter($container);
    }

    /**
     * @throws Exception
     */
    abstract protected function loadBundleServices(ContainerBuilder $container): void;

    /**
     * @throws Exception
     */
    private function loadCoreServices(ContainerBuilder $container): void
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
