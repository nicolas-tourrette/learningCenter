<?php

// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

use App\Service\JsonParser as Json;

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
    * @Route("/apps/{name}", name="appDetails")
    */
    public function appDetails(Request $request, $name){
        $em = $this->getDoctrine()->getManager();
        $appDetails = $em->getRepository("App:App")->findOneByAppCode($name);

        if($appDetails == null){
            throw new NotFoundHttpException("Error code #2000 — Application inconnue.");
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
        $json = new Json("assets/versions.json");

        return $this->render('app/version.html.twig', array(
            'listVersions' => $json->parseJson()
        ));
    }

    public function getLastVersion(){
        $json = new Json("assets/versions.json");

        return $this->render('app/foot-version.html.twig', array(
            'lastVersion' => $json->parseJson()[0]
        ));
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