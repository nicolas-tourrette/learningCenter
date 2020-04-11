<?php
// src/Service/LoggedUser.php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;

use DateInterval;

class LoggedUser
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        // Update your field here.
        $user->setLastLogin(new \DateTime());
        $user->setLastIP($_SERVER['REMOTE_ADDR']);

        $paiement = $user->getPaiementType();
        $date = $user->getPaiementDate();
        $dateNow = new \Datetime();

        if($paiement == "month"){
            if($dateNow > $date->add(new DateInterval('P1M'))){
                $user->setIsActive(false);
                $user->setPaiementStatus(false);
            }
        }
        elseif($paiement == "year"){
            if($dateNow > $date->add(new DateInterval('P1Y'))){
                $user->setIsActive(false);
                $user->setPaiementStatus(false);
            }
        }
        elseif($paiement == "due" && $dateNow > $date->add(new DateInterval('P14D'))){
            $user->setIsActive(false);
            $user->setPaiementStatus(false);
        }

        // Persist the data to database.
        $this->em->persist($user);
        $this->em->flush();
    }
}