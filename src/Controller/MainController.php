<?php

// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use App\Service\JsonParser as Json;

use App\Form\SupportContactType;

class MainController extends AbstractController {
    /**
    * @Route("/", name="index")
    */
    public function index(Request $request){
        
        return $this->render('index.html.twig', array(
                'lastVersion' => $this->getLastVersion()
        ));
    }

    /**
    * @Route("/about", name="about")
    */
    public function about(){
        
        return $this->render('app/about.html.twig');
    }

    /**
    * @Route("/support", name="support")
    */
    public function support(Request $request, MailerInterface $mailer){
        $form = $this->get('form.factory')->create(SupportContactType::class);

        if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $message = new TemplatedEmail();

			    $message
                    ->from(new Address($data["email"], $data["name"]))
                    ->to(new Address('support@learnapp.nicolas-t.ovh', "Support LearnApp"))
                    ->cc(new Address($data["email"], $data["name"]))
                    //>bcc(new Address('postmaster@nicolas-t.ovh', 'LearnApp'))
                    ->replyTo(new Address('support@learnapp.nicolas-t.ovh', "Support LearnApp"))
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject($data["subject"])
                    ->htmlTemplate('email/contact.html.twig')
                    ->context(array(
                        'message' => $data,
                        'client' => $this->getIp()
                    ))
                ;

                $mailer->send($message);

                $request->getSession()->getFlashBag()->add('success', "Votre message a été soumis au Support, vous recevrez également la copie de votre demande.");
                return $this->redirectToRoute('support');
			}
		}
        
        return $this->render('app/support.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
    * @Route("/apps/{name}", name="appDetails")
    */
    public function appDetails(Request $request, $name){
        $em = $this->getDoctrine()->getManager();
        $appDetails = $em->getRepository("App:App")->findOneByAppCode($name);

        if($appDetails == null){
            $json = new Json();
            throw new NotFoundHttpException($json->throwErrorMessage("e3001"));
        }
        
        $json = new Json("assets/datas/".$name."/versions.json");

        $request->getSession()->getFlashBag()->add('warning', "TODO");
        return $this->render('app/aboutapp.html.twig', array(
            'appDetails' => $appDetails,
            'listVersions' => $json->parseJson()
        ));
    }

    /**
     * @Route("/about/version", name="version")
     */
    public function version(){
        $json = new Json("assets/datas/versions.json");

        return $this->render('app/version.html.twig', array(
            'listVersions' => $json->parseJson()
        ));
    }

    public function getLastVersion(){
        $json = new Json("assets/datas/versions.json");

        return $this->render('app/foot-version.html.twig', array(
            'lastVersion' => $json->parseJsonFirst()
        ));
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

    /*public function getVersions($jsonFile){
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $jsonDatas;
        }
        return null;
    }*/
}