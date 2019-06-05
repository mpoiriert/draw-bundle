<?php

namespace Draw\DrawBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('draw');
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->booleanNode('use_jms_serializer')->defaultTrue()->end()
                    ->booleanNode('use_api_exception_subscriber')->defaultTrue()->end()
                    ->booleanNode('use_doctrine_repository_factory')->defaultTrue()->end()
                ->end();

        return $treeBuilder;
    }
}
