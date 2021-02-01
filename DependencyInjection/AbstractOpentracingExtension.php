<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\DependencyInjection;

use Exception;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class AbstractOpentracingExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     * @throws Exception
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadBundleServices($container);
        $this->loadCoreServices($container);
        $this->overwriteProjectNameParameter($container);
        $this->addTagsForPSR18Clients($container);
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
        $projectDirectory = (string) $container->getParameter('kernel.project_dir');
        $container->setParameter('env(AUXMONEY_OPENTRACING_PROJECT_NAME)', basename($projectDirectory));
    }

    private function addTagsForPSR18Clients(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ClientInterface::class)->addTag(PSR18CompilerPass::TAG_PSR_18);
    }
}
