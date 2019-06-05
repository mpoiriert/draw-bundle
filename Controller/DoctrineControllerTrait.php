<?php namespace Draw\DrawBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;

trait DoctrineControllerTrait
{
    /**
     * @return Registry
     */
    abstract public function getDoctrine();

    public function repository($class)
    {
        return $this->getDoctrine()->getRepository($class);
    }

    public function persistAndFlush($entity)
    {
        $manager = $this->getDoctrine()->getManagerForClass(get_class($entity));
        $manager->persist($entity);
        $manager->flush();
    }

    public function flush($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->getDoctrine()->getManagerForClass($class)->flush();
    }

    public function flushAll($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->getDoctrine()->getManagerForClass($class)->flush();
    }

    public function persist($entity)
    {
        $this->getDoctrine()->getManagerForClass(get_class($entity))->persist($entity);
    }

    public function removeAndFlush($entity)
    {
        $manager = $this->getDoctrine()->getManagerForClass(get_class($entity));
        $manager->remove($entity);
        $manager->flush();
        return $entity;
    }
}