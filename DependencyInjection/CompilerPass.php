<?php

namespace Draw\DrawBundle\DependencyInjection;

use Draw\DrawBundle\Request\RequestBodyParamConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
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
        $requestBodyConverter = null;
        if($container->hasDefinition("fos_rest.converter.request_body")) {
            $requestBodyConverter = $container->getDefinition("fos_rest.converter.request_body");
        } elseif($container->hasDefinition(\FOS\RestBundle\Request\RequestBodyParamConverter::class)) {
            $requestBodyConverter = $container->getDefinition(\FOS\RestBundle\Request\RequestBodyParamConverter::class);
        }

        if(!is_null($requestBodyConverter)) {
            $requestBodyConverter->setClass(RequestBodyParamConverter::class);
            $requestBodyConverter->addMethodCall(
                "setGroupHierarchy",
                [new Reference("draw.serializer.group_hierarchy")]
            );
        }

        if($container->hasDefinition('jms_serializer.doctrine_object_constructor')) {
            $container->getDefinition("jms_serializer.doctrine_object_constructor")
                ->setClass('Draw\DrawBundle\Serializer\Construction\DoctrineObjectConstructor');
        }
    }
}