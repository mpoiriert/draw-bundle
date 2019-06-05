<?php

namespace Draw\DrawBundle\DependencyInjection;

use Draw\DrawBundle\Doctrine\Repository\Factory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineServiceRepositoryCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        try {
            $factory = $container->findDefinition(Factory::class);
            $configurationDefinition = $container->findDefinition('doctrine.orm.configuration');
        } catch (ServiceNotFoundException $e) {
            //The configuration draw.use_doctrine_repository_factory is probably set to false
            //Or doctrine.orm.configuration is not available because of doctrine bundle not present
            return;
        }

        $repositories = [];
        foreach ($container->findTaggedServiceIds('draw.doctrine.repository') as $id => $params) {
            foreach ($params as $param) {
                $repositories[$param['class']] = $id;
                $repository = $container->findDefinition($id);
                $repository->replaceArgument(0, new Reference('doctrine.orm.default_entity_manager'));
                $definition = new Definition();
                $definition->setClass('Doctrine\ORM\Mapping\ClassMetadata');
                $definition->setFactory(['doctrine.orm.default_entity_manager', 'getClassMetadata']);
                $definition->setArguments([$param['class']]);
                $repository->replaceArgument(1, $definition);
            }
        }

        $factory->replaceArgument('$ids', $repositories);
        $configurationDefinition->addMethodCall('setRepositoryFactory', [$factory]);
    }
}