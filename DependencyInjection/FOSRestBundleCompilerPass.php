<?php namespace Draw\DrawBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class FOSRestBundleCompilerPass implements CompilerPassInterface
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
            $exceptionSubscriberDefinition = $container->findDefinition('draw.exception_subscriber');
        } catch (ServiceNotFoundException $e) {
            //The configuration draw.use_api_exception_subscriber is probably set to false
            return;
        }

        // This is to be compatible with old and new version for FOSRestBundle
        if(!$container->hasParameter('fos_rest.exception.codes')) {
            if($container->hasDefinition('fos_rest.exception.codes_map')) {
                $exceptionCodes = $container->getDefinition('fos_rest.exception.codes_map')->getArgument(0);
                $container->setParameter('fos_rest.exception.codes', $exceptionCodes);
            }
        }

        if($container->hasParameter('fos_rest.exception.codes')) {
            $exceptionSubscriberDefinition->setArgument(2, $container->getParameter('fos_rest.exception.codes'));
        }
    }
}