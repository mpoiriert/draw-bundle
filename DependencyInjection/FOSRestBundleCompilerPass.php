<?php namespace Draw\DrawBundle\DependencyInjection;

use Draw\DrawBundle\EventListener\ApiExceptionSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
            $exceptionSubscriberDefinition = $container->findDefinition(ApiExceptionSubscriber::class);
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
            $exceptionSubscriberDefinition->setArgument('$exceptionCodes', $container->getParameter('fos_rest.exception.codes'));
        }
    }
}