<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle;

use Auxmoney\OpentracingBundle\DependencyInjection\PSR18CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractOpentracingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PSR18CompilerPass());
    }
}
