<?php namespace Draw\DrawBundle\Security;

interface OwnedByInterface
{
    /**
     * @return null|OwnerInterface
     */
    public function getOwnedBy();
}