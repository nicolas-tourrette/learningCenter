<?php
// src/Service/NewUserListener.php

namespace App\Service;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Service\ApplicationMailer;
use App\Entity\User;

class NewUserListener
{
    /**
     * @var ApplicationMailer
     */
    private $applicationMailer;

    public function __construct(ApplicationMailer $applicationMailer)
    {

        $this->applicationMailer = $applicationMailer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // On ne veut envoyer un email que pour les entités Application
        if ($entity instanceof User) {
            $this->applicationMailer->sendNotificationNewUser($entity);
        }
        else{
            return;
        }

        
    }
}
