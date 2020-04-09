<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException(
                'Compte désactivé.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->checkPreAuth($user);
    }
}