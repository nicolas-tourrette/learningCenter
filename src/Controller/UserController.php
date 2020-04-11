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

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('login', array('last_username' => $this->getUser()->getUsername()));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()) {
                $user->setPassword($passwordEncoder->encodePassword(
					$user,
					$user->getPassword()
                ));

                if($user->getRoles() === array("ROLE_USER")){
                    $user->setPaiementStatus(true);
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
                    $user->setPaiementType("due");

                    $mailer->send($message);
                }

                $user->setPaiementDate(new \DateTime());
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

    /**
     * @Route("/compte/myapps", name="addApps")
     */
    public function compteUpdateApps(Request $request, MailerInterface $mailer){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('login', array('last_username' => $this->getUser()->getUsername()));
        }

        $jsonFile = "assets/datas/apps.json";
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);
        }
        else{
            throw $this->createNotFoundException('Impossible de charger la liste des applications.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $apps = $this->getUser()->getApps();

        $choices = array();
        foreach($jsonDatas as $app){
            if(!in_array($app["appName"], $apps)){
                $label = $app["appName"].' (v.'.$app["version"].')';
                $choice = array($label => $app["appName"]);
                array_push($choices, $choice);
            }
        }

        if($choices == []){
            $request->getSession()->getFlashBag()->add('warning', 'Aucune application disponible !');
            return $this->redirectToRoute('compte');
        }

        $form = $this->createFormBuilder()
            ->add('apps',
                ChoiceType::class,
                array(
                    'label' => "Applications disponibles à l'ajout",
                    'required' => true,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $choices,
                    'choice_attr' => function($choice, $key, $value) {
                        return ['class' => "form-check-input"];
                    },
                    'attr' => ['class' => "form-check"]
                )
            )
            ->add('submit',
                SubmitType::class,
                array(
                    'label' => "Valider mon choix d'applications",
                    'attr' => ['class' => "btn btn-primary"]
                )
            )
            ->getForm()
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()) {

                $bool = $user->addApps($form->getData()["apps"]);
                if($bool === null){ $bool = true; }

                if($bool === false){
                    $request->getSession()->getFlashBag()->add('danger', 'Avec votre sélection, vous dépassez le nombre maximal d\'applications pour votre abonnement. Veuillez essayer avec moins d\'applications dans votre panier.');
                    return $this->redirectToRoute('compte');
                };
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
        
        return $this->render('app/account/add_apps.html.twig', array(
			'form' => $form->createView()
        ));
    }

    /**
     * @Route("/compte/myapps/delete/{appname}", name="deleteApp")
     */
    public function compteDeleteApp(Request $request, $appname){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			return $this->redirectToRoute('login', array('last_username' => $this->getUser()->getUsername()));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->removeApp($appname);
        $user->setUpdated(new \DateTime());
        $em->persist($user);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', "L'application a été retirée avec succès de votre compte.");
        return $this->redirectToRoute('compte');
    }
}