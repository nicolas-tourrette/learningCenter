<?php
// src/Controller/SecurityController.php;

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Services\Mailer;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use App\Form\UserType;

use App\Entity\User;
//use App\Entity\Log;


class SecurityController extends AbstractController
{
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
	{
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();
        $authFull = $request->query->get('authFull');

        if($error !== null){
            /*$em = $this->getDoctrine()->getManager();
            $log = new Log();
            $log->setLevel("danger");
            $log->setMessage("Échec de la tentative de connexion de l'utilisateur ".$lastUsername." : ".$error->getMessageKey());
            $em->persist($log);
            $em->flush();*/
        }

        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') && $referer = $request->headers->get('referer') == null) {
            return $this->redirectToRoute('index');
        }
		
		return $this->render('security/login.html.twig', array(
			'last_username' => $lastUsername,
			'error'         => $error
		));
    }

	/**
	 * @Route("/register", name="register")
	 */
	public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder){
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw new AccessDeniedHttpException('Vous êtes déjà un utilisateur authentifié.');
		}

		$em = $this->getDoctrine()->getManager();
		$user = new User();

		$form = $this->get('form.factory')->create(UserType::class, $user);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$user->setPassword($passwordEncoder->encodePassword(
					$user,
					$user->getPassword()
                ));

                if($user->getRoles() === array() || in_array("ROLE_USER", $user->getRoles())){
                    if($user->getRoles() === array()){
                        $user->setRoles(array("ROLE_USER"));
                    }
                    $user->setPaiementStatus(true);
                    $user->setPaiementDate(new \DateTime());
                    $user->setPaiementType("free");
                }

                $em->persist($user);

                /*$log = new Log();
                $log->setLevel("success");
                $log->setMessage("Création du compte de l'utilisateur ".$user->getUsername()." effectuée avec succès.");
                $em->persist($log);*/

				$em->flush();

				return $this->redirectToRoute('login', array('last_username' => $user->getUsername()));
			}
		}

		return $this->render('security/register.html.twig', array(
			'form' => $form->createView(),
		));
	}

	/**
     * @Route("/forgottenpass", name="forgottenPass")
     */
    public function request(Request $request, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        // création d'un formulaire "à la volée", afin que l'internaute puisse renseigner son mail
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => "Adresse e-mail",
                'required' => true,
                'attr' => ["placeholder" => "prenom.nom@fournisseur.fr", 'class' => "form-control"],
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->add('submit',
                SubmitType::class,
                array(
                    'label' => "Valider la demande",
                    'attr' => ['class' => "btn btn-primary"]
                )
            )
			->getForm();
			
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // voir l'épisode 2 de cette série pour retrouver la méthode loadUserByUsername:
            $user = $em->getRepository(User::class)->findOneByEmail($form->getData()['email']);

            // aucun email associé à ce compte.
            if (!$user) {
                $request->getSession()->getFlashBag()->add('warning', "Cet email n'existe pas.");
                return $this->redirectToRoute("request_resetting");
            } 

			// création du token
            $user->setToken($tokenGenerator->generateToken());
            // enregistrement de la date de création du token
            $user->setPasswordRequestedAt(new \Datetime());

            /*$log = new Log();
            $log->setLevel("info");
            $log->setMessage("Perte du mot de passe par l'utilisateur ".$user->getUsername().".");
            $em->persist($log);*/

            $em->flush();

            $message = new TemplatedEmail();

			$message
				->from(new Address('noreply@learnapp.nicolas-t.ovh', 'Plateforme LearnApp'))
				->to(new Address($user->getEmail(), $user->getName()))
				//->cc('cc@example.com')
				->bcc(new Address('postmaster@nicolas-t.ovh', 'Administrateur Plateforme LearnApp'))
				->replyTo(new Address('postmaster@nicolas-t.ovh', 'No-Reply Plateforme LearnApp'))
				//->priority(Email::PRIORITY_HIGH)
				->subject('Perte de votre mot de passe LearnApp')
				->htmlTemplate('email/lost_password.html.twig')
				->context(array(
					'user' => $user,
                    'client' => $this->getIp()
				))
			;
           
            $mailer->send($message);

            $request->getSession()->getFlashBag()->add('success', "Un mail va vous être envoyé afin que vous puissiez renouveller votre mot de passe. Le lien que vous recevrez sera valide 24h.");

            return $this->redirectToRoute("index");
        }

        return $this->render('security/lost_password_request.html.twig', [
            'form' => $form->createView()
        ]);
	}
	
	// si supérieur à 24h, retourne false
    // sinon retourne false
    private function isRequestInTime(\Datetime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null)
        {
            return false;        
        }
        
        $now = new \DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        $daySeconds = 3600 * 24;
        $response = $interval > $daySeconds ? false : $reponse = true;
        return $response;
    }

    /**
     * @Route("/forgottenpass/{username}/{token}", name="resettingPassword")
     */
    public function resettingPassword(User $user, $token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $em = $this->getDoctrine()->getManager();
        // interdit l'accès à la page si:
        // le token associé au membre est null
        // le token enregistré en base et le token présent dans l'url ne sont pas égaux
        // le token date de plus de 24h
        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt()))
        {
            /*$log = new Log();
            $log->setLevel("danger");
            $log->setMessage("Échec de la récupération de mot de passe de l'utilisateur ".$user->getUsername()." : token invalide.");
            $em->persist($log);
            $em->flush();*/
            throw new AccessDeniedHttpException('Token invalide.');
        }

        $form = $this->createFormBuilder()
            ->add('password',
                RepeatedType::class,
                array(
                    'type' => PasswordType::class,
                    'label' => "Mot de passe",
                    'required' => true,
                    'options' => ['attr' => ['class' => 'password-field']],
                    'invalid_message' => 'Les  mots de passe doivent correspondre.',
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmer le mot de passe'],
                    'attr' => [ 'class' => "form-control" ]
                )

            )
            ->add('submit',
                SubmitType::class,
                array(
                    'label' => "Valider le changement",
                    'attr' => ['class' => "btn btn-primary"]
                )
            )
			->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($passwordEncoder->encodePassword(
				$user,
				$form->getData()['password']
			));

            // réinitialisation du token à null pour qu'il ne soit plus réutilisable
            $user->setToken(null);
            $user->setPasswordRequestedAt(null);
            
            $em->persist($user);

            /*$log = new Log();
            $log->setLevel("success");
            $log->setMessage("Mot de passe de l'utilisateur ".$user->getUsername()." modifié avec succès.");
            $em->persist($log);*/

            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Votre mot de passe a été réinitialisé.");

            return $this->redirectToRoute('login');
        }

        return $this->render('Security/lost_password.html.twig', [
            'form' => $form->createView()
        ]);
        
    }

    private function getIp(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}
