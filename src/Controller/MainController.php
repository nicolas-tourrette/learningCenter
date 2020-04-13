<?php

// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

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
    public function appDetails($name){
        $em = $this->getDoctrine()->getManager();
        $appDetails = $em->getRepository("App:App")->findOneByAppCode($name);

        if($appDetails == null){
            throw new NotFoundHttpException("Cette application n'existe pas.");
        }

        return $this->render('app/aboutapp.html.twig', array(
            'appDetails' => $appDetails,
            'listVersions' => $this->getVersions("assets/datas/".$name."/versions.json")
        ));
    }

    /**
     * @Route("/about/version", name="version")
     */
    public function version(){
        return $this->render('app/version.html.twig', array(
            'listVersions' => $this->getVersions("assets/versions.json")
        ));
    }

    public function getLastVersion(){
        $jsonFile = "assets/versions.json";
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $this->render('app/foot-version.html.twig', array(
                'lastVersion' => $jsonDatas[0]
            ));
        }

        return $this->render('app/version.html.twig', array(
            'lastVersion' => null
        ));
    }

    public function getVersions($jsonFile){
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $jsonDatas;
        }
        return null;
    }
}