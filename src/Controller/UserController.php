<?php

// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Twig\Environment;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use App\Form\UserType;

use App\Entity\User;
//use App\Entity\Log;

/**
 * @Route("/me")
 */
class UserController extends AbstractController {
    
    /**
     * @Route("/compte", name="compte")
     */
    public function compte(){
        return $this->render('app/account/profil.html.twig');
    }

    /**
     * @Route("/compte/update", name="update")
     */
    public function compteUpdate(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('login', array('last_username' => $this->getUser()->getUsername()));
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()) {
                $user->setPassword($passwordEncoder->encodePassword(
					$user,
					$user->getPassword()
                ));

                dump($user->getRoles());

                if($user->getRoles() === array("ROLE_USER")){
                    $user->setPaiementStatus(true);
                    $user->setPaiementDate(new \DateTime());
                    $user->setPaiementType("free");
                }
                else{
                    $message = new TemplatedEmail();

                    $message
                        ->from(new Address('learnapp@nicolas-t.ovh', 'Plateforme LearnApp'))
                        ->to(new Address($user->getEmail(), $user->getName()))
                        //->cc('cc@example.com')
                        ->bcc(new Address('postmaster@nicolas-t.ovh', 'Administrateur Plateforme LearnApp'))
                        ->replyTo(new Address('postmaster@nicolas-t.ovh', 'No-Reply - Plateforme LearnApp'))
                        ->priority(Email::PRIORITY_HIGH)
                        ->subject('Facturation de votre nouvel abonnement LearnApp')
                        ->htmlTemplate('email/new_account_bill.html.twig')
                        ->context(array(
                            'user' => $user
                        ))
                    ;

                    $user->setPaiementStatus(false);
                    $user->setPaiementDate(null);
                    $user->setPaiementType("");

                    $mailer->send($message);
                }

                $user->setUpdated(new \DateTime());

                $em->persist($user);

                /*$log = new Log();
                $log->setLevel("success");
                $log->setMessage("Compte de l'utilisateur ".$user->getUsername()." mis à jour avec succès.");
                $em->persist($log);*/

				$em->flush();

				$request->getSession()->getFlashBag()->add('success', 'Votre compte a bien été mis à jour !');
                return $this->redirectToRoute('compte');
            }
            else{
                /*$log = new Log();
                $log->setLevel("danger");
                $log->setMessage("Échec de la mise à jour du compte de l'utilisateur ".$user->getUsername().".");
                $em->persist($log);

				$em->flush();*/
            }
        }
        
        return $this->render('app/account/update.html.twig', array(
            'user' => $user,
			'form' => $form->createView()
        ));
    }
}