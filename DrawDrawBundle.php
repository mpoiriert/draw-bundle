<?php

namespace Draw\DrawBundle;

use Draw\DrawBundle\DependencyInjection\CompilerPass;
use Draw\DrawBundle\DependencyInjection\DoctrineServiceRepositoryCompilerPass;
use Draw\DrawBundle\DependencyInjection\DrawDrawExtension;
use Draw\DrawBundle\DependencyInjection\FOSRestBundleCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawDrawBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
        $container->addCompilerPass(new DoctrineServiceRepositoryCompilerPass());
        $container->addCompilerPass(new FOSRestBundleCompilerPass());
    }

    public function getContainerExtension()
    {
        return new DrawDrawExtension();
    }
}
