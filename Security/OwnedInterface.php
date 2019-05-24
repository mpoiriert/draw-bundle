<?php

namespace Draw\DrawBundle\Security;

interface OwnedInterface
{
    /**
     * Return if the object is owned by the possible owner
     *
     * @param OwnerInterface $possibleOwner
     * @return boolean
     */
    public function isOwnedBy(OwnerInterface $possibleOwner);
}