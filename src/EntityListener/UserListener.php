<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener 
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function prePersist(User $user)
    {
        $this->hashPassword($user);
    }

    public function preUpdate(User $user)
    {
        $this->hashPassword($user);
    }

    /**
     * Encode password based on plainPassword
     */
    public function hashPassword(User $user)
    {
        if($user->getPassword() === null) {
            return;
        } 

        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
    }

}