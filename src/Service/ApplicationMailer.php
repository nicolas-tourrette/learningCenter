<?php
// src/Service/ApplicationMailer.php

namespace App\Service;

use App\Entity\User;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ApplicationMailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNotificationNewUser(User $user)
    {
        $message = new TemplatedEmail();

        $message
            ->from(new Address('learnapp@nicolas-t.ovh', 'Plateforme LearnApp'))
            ->to(new Address($user->getEmail(), $user->getName()))
            //->cc('cc@example.com')
            ->bcc(new Address('postmaster@nicolas-t.ovh', 'Administrateur Plateforme LearnApp'))
            ->replyTo(new Address('postmaster@nicolas-t.ovh', 'No-Reply - Plateforme LearnApp'))
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Création de votre compte LearnApp')
            ->htmlTemplate('email/new_account_message.html.twig')
            ->context(array(
                'user' => $user
            ))
        ;

        $this->mailer->send($message);
        
        if(in_array("ROLE_USER-PLUS", $user->getRoles()) || in_array("ROLE_USER-PREMIUM", $user->getRoles())){
            $message
                ->htmlTemplate('email/new_account_bill.html.twig')
                ->subject('Facturation de votre abonnement LearnApp')
            ;

            $this->mailer->send($message);
        }
    }
}
