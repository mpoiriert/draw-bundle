<?php namespace Draw\DrawBundle\Security\Voter;

use Draw\DrawBundle\Security\OwnedByInterface;
use Draw\DrawBundle\Security\OwnedInterface;
use Draw\DrawBundle\Security\OwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class OwnVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        return $attribute == "OWN";
    }

    public function supportsClass($class)
    {
        $class = new \ReflectionClass($class);

        return
            $class->implementsInterface(OwnedInterface::class)
            || $class->implementsInterface(OwnedByInterface::class);
    }

    /**
     * @param TokenInterface $token
     * @param null|OwnedInterface|OwnedByInterface $object
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if(!is_object($object)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (!$this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();
        if (!$user instanceof OwnerInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if($object instanceof OwnedInterface) {
                return $object->isOwnedBy($user) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
            } elseif($object instanceof OwnedByInterface) {
                $ownedBy = $object->getOwnedBy();
                if(is_null($ownedBy)) {
                    continue;
                }

                return $ownedBy->getOwnerId() == $user->getOwnerId()
                    ? VoterInterface::ACCESS_GRANTED
                    : VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
