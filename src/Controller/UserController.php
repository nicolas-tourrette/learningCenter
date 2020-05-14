<?php

// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $apps = array();

        foreach($user->getApps() as $app){
            $thisApp = $em->getRepository("App:App")->findByAppCode($app);
            array_push($apps, ['appId' => $thisApp[0]->getAppCode(), 'appName' => $thisApp[0]->getAppName()]);
        }

        return $this->render('app/account/profil.html.twig', array(
            'apps' => $apps
        ));
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
        
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $subscribaleApps = $em->getRepository("App:App")->findMySubscribaleApps($user->getPartnerSchool());
        $apps = $this->getUser()->getApps();

        $counter = count($apps) - count(preg_grep("/partner-*/", $apps));
        if($this->isGranted("ROLE_USER")){
            $remainingApps = 2-$counter;
        }
        elseif ($this->isGranted("ROLE_USER-PLUS")) {
            $remainingApps = 4-$counter;
        }
        else{
            $remainingApps = "UNLIMITED";
        }
        
        $choices = array();
        foreach ($subscribaleApps as $key => $value) {
            if(!in_array($value->getAppCode(), $apps)){
                $label = $value->getAppName().' (v.'.$value->getAppVersion().')';
                $choice = array($label => $value->getAppCode());
                array_push($choices, $choice);
            }
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
                        return ['class' => "custom-control-input"];
                    }
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
                $subscribtion = $form->getData()["apps"];
                $appsToAdd = array();
                $partnerApps = $em->getRepository("App:App")->findByAppPartnerSchool($user->getPartnerSchool());

                foreach ($partnerApps as $key => $value) {
                    if(in_array($value->getAppCode(), $subscribtion)){
                        array_push($appsToAdd, $value->getAppCode());
                        unset($subscribtion[array_search($value->getAppCode(), $subscribtion)]);
                    }
                }
                $user->addPartnerApps($appsToAdd);

                $bool = $user->addApps($subscribtion, $remainingApps);
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
            'remainingApps' => $remainingApps,
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

    private function purgeInformations(){
        $em = $this->getDoctrine()->getManager();

        $date = new \Datetime("30 days ago");
        $listInfos = $em->getRepository("App:Information")->getInformationsOlderThan($date);

        foreach($listInfos as $info){
            $em->remove($info);
        }
        $em->flush(); 
    }

    public function countInformations(){
        $em = $this->getDoctrine()->getManager();
        $this->purgeInformations();

        $informations = $em->getRepository("App:Information")->findAll();

        return new Response(count($informations));
    }

    /**
     * @Route("/compte/informations/{page}", name="informations", requirements={"page" = "\d+"}, defaults={"page" = 1})
     */
    public function informations($page){
        $nbPerPage = 10;
        if ($page < 1) {
            throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $informations = $em->getRepository("App:Information")->getInformations($page, $nbPerPage);

        if(count($informations) == 0){
            $informations = "Pas d'information disponible pour le moment. Repassez plus tard...";
            $nbPages = 1;
        }
        else{
            $nbPages = ceil(count($informations)/$nbPerPage);
        }

        if($page > $nbPages){
            throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
        }

        return $this->render('app/account/informations.html.twig', array(
            'informations' => $informations,
            'nbPages' => $nbPages,
            'page' => $page
        ));
    }

    public function countNotifications(SessionInterface $session){
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository("App:Notification")->findByRecipient($this->getUser());

        return new Response(count($notifications));
    }

    public function countUnreadNotifications(SessionInterface $session){
        $em = $this->getDoctrine()->getManager();

        $notifications = $em->getRepository("App:Notification")->findBy(array("recipient" => $this->getUser(), "status" => false));
        
        if(count($notifications) > 0){
            return new Response('<span class="badge noti-badge badge-warning"></span>');
        }
        else{
            return new Response();
        }
    }

    /**
     * @Route("/compte/notifications/{page}", name="notifications", requirements={"page" = "\d+"}, defaults={"page" = 1})
     */
    public function notifications($page){
        $nbPerPage = 20;
        if ($page < 1) {
            throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
        }
        
        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository("App:Notification")->getNotifications($this->getUser()->getId(), $page, $nbPerPage);

        if(count($notifications) == 0){
            $notifications = "Vous n'avez pas de notification.";
            $nbPages = 1;
        }
        else{
            $nbPages = ceil(count($notifications)/$nbPerPage);
        }

        if($page > $nbPages){
            throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
        }

        return $this->render('app/account/notifications.html.twig', array(
            'notifications' => $notifications,
            'nbPages' => $nbPages,
            'page' => $page
        ));
    }

    /**
     * @Route("/compte/notifications/action/{action}/{id}", name="notificationsAction", requirements={"action" = "read|unread|trash", "id" = "\d+"})
     */
    public function notificationsAction(Request $request, $action, $id){
        if(!in_array($action, array("read", "unread", "trash"))){
            throw new \Exception("Action de notification inconnue.");
        }

        $em = $this->getDoctrine()->getManager();
        $notification = $em->getRepository("App:Notification")->find($id);

        if ($notification == null) {
			throw new NotFoundHttpException("Cette notification n'existe pas.");
		}

        if($action == "read"){
            $notification->setStatus(true);
            $em->persist($notification);
            $request->getSession()->getFlashBag()->add('info', 'La notification a été marquée comme lue.');
        }
        elseif($action == "unread"){
            $notification->setStatus(false);
            $em->persist($notification);
            $request->getSession()->getFlashBag()->add('info', 'La notification a été marquée comme non lue.');
        }
        elseif($action == "trash"){
            $em->remove($notification);
            $request->getSession()->getFlashBag()->add('info', 'La notification a été supprimée.');
        }

        $em->flush();
        return $this->redirectToRoute('notifications');
    }
}