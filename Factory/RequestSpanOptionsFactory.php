<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Auxmoney\OpentracingBundle\Internal\Utility;
use PackageVersions\Versions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

final class RequestSpanOptionsFactory implements SpanOptionsFactory
{
    private Utility $utility;
    private string $kernelDebug;
    private string $kernelEnvironment;
    private string $hostName;

    public function __construct(
        Utility $utility,
        string $kernelDebug,
        string $kernelEnvironment,
        string $hostName
    ) {
        $this->utility = $utility;
        $this->kernelDebug = $kernelDebug;
        $this->kernelEnvironment = $kernelEnvironment;
        $this->hostName = $hostName;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function createSpanOptions(Request $request = null): array
    {
        $options = [
            'tags' => [
                'kernel.debug' => $this->kernelDebug ? 'true' : 'false',
                'kernel.environment' => $this->kernelEnvironment,
                'symfony.version' => Kernel::VERSION,
                'opentracing.version' => Versions::getVersion('auxmoney/opentracing-bundle-core'),
                'pod/host' => $this->hostName,
                'php.version' => PHP_VERSION,
            ]
        ];

        if ($request) {
            $externalSpanContext = $this->utility->extractSpanContext($request->headers->all());
            if ($externalSpanContext) {
                $options['child_of'] = $externalSpanContext;
            }
        }

        return $options;
    }
}
