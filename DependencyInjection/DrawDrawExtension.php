<?php

namespace Draw\DrawBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DrawDrawExtension extends ConfigurableExtension
{
    /**
     * {@inheritDoc}
     */
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        if($config['use_api_exception_subscriber']) {
            $loader->load('api_exception_subscriber.yaml');
        }

        if($config['use_doctrine_repository_factory']) {
            $loader->load('doctrine_repository_factory.yaml');
        }

        if($config['use_jms_serializer']) {
            $loader->load('jms_serializer.yaml');
        }
    }

    public function getAlias()
    {
        return 'draw';
    }
}
