<?php

namespace Draw\DrawBundle\Serializer;

use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SelfLinkEventListener
{
    private $entitiesRoutes = array();

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $addClass = true;

    /**
     * @var array
     */
    private $classRouteInfo = array();

    public function __construct(ContainerInterface $container, $entitiesRoutes = array())
    {
        $this->container = $container;
        $this->entitiesRoutes = $entitiesRoutes;
    }

    /**
     * If we must add the class attribute on serialization
     *
     * @param $addClass
     */
    public function setAddClass($addClass)
    {
        $this->addClass = $addClass;
    }

    public function onPostSerialize(ObjectEvent $objectEvent)
    {
        if(!class_exists(ClassUtils::class)) {
            return;
        }

        $visitor = $objectEvent->getVisitor();
        $object = $objectEvent->getObject();
        $router = $this->container->get("router");
        try {
            $class = ClassUtils::getClass($objectEvent->getObject());
            if ($this->addClass) {
                $visitor->addData('_class', $class);
            }

            $routeInfo = $this->getClassRouteInfo($class);
            if (!is_null($routeInfo)) {
                $parameters = [];
                foreach ($routeInfo[1] as $parameter => $propertyPath) {
                    $parameters[$parameter] = $this->container->get("property_accessor")
                        ->getValue($object, $propertyPath);
                }
                $visitor->addData(
                    '_href',
                    $router->generate($routeInfo[0], $parameters, UrlGeneratorInterface::ABSOLUTE_URL)
                );
            }
        } catch (\InvalidArgumentException $e) {

        }
    }

    private function getClassRouteInfo($class)
    {
        if (array_key_exists($class, $this->classRouteInfo)) {
            return $this->classRouteInfo[$class];
        }

        foreach ($this->entitiesRoutes as $entityClass => $routeName) {
            if ($class != $entityClass && !is_subclass_of($class, $entityClass)) {
                continue;
            }

            return $this->classRouteInfo[$class] = [$routeName, ['id' => 'id']];
        }

        foreach ($this->container->get("router")->getRouteCollection()->all() as $routeName => $route) {
            $options = $route->getOptions();
            if (!isset($options['draw']['href']['class'])) {
                continue;
            }

            $entityClass = $options['draw']['href']['class'];
            if ($class != $entityClass && !is_subclass_of($class, $entityClass)) {
                continue;
            }

            if (isset($options['draw']['href']['parameterPaths'])) {
                $parameterPaths = $options['draw']['href']['parameterPaths'];
            } else {
                $parameterPaths = ['id' => 'id'];
            }

            return $this->classRouteInfo[$class] = [$routeName, $parameterPaths];
        }
    }
}