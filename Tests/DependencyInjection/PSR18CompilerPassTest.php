<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\DependencyInjection;

use Auxmoney\OpentracingBundle\DependencyInjection\PSR18CompilerPass;
use Auxmoney\OpentracingBundle\Internal\Decorator\PSR18ClientDecorator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PSR18CompilerPassTest extends TestCase
{
    /** @var PSR18CompilerPass */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new PSR18CompilerPass();
    }

    public function testProcessNoTagsFound(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);
        $container->findTaggedServiceIds('auxmoney_opentracing.psr_18')->willReturn([]);

        $container->register(Argument::any())->shouldNotBeCalled();

        $this->subject->process($container->reveal());
    }

    public function testProcessSuccess(): void
    {
        $container = $this->prophesize(ContainerBuilder::class);
        $container->findTaggedServiceIds('auxmoney_opentracing.psr_18')->willReturn(['serviceId' => []]);

        $definition = $this->prophesize(Definition::class);
        $definition->setDecoratedService('serviceId')->shouldBeCalled()->willReturn($definition);
        $definition->setArgument(0, Argument::type(Reference::class))->shouldBeCalled()->willReturn($definition);
        $definition->setPublic(false)->shouldBeCalled()->willReturn($definition);
        $definition->setAutowired(true)->shouldBeCalled()->willReturn($definition);
        $container->register(Argument::containingString('.serviceId'), PSR18ClientDecorator::class)->shouldBeCalled()
            ->willReturn($definition->reveal());

        $this->subject->process($container->reveal());
    }
}
