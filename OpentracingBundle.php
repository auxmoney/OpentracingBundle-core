<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpentracingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // TODO: how to handle passes?
    }
}
