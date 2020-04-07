<?php

// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
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
    public function about(Request $request){
        
        return $this->render('app/about.html.twig');
    }

    /**
    * @Route("/apps/geobrevet", name="geobrevet")
    */
    public function geobrevet(Request $request){
        
        return $this->render('app/geobrevet/about.html.twig');
    }

    public function getLastVersion(){
        $jsonFile = "assets/version.json";
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $this->render('app/version.html.twig', array(
                'lastVersion' => $jsonDatas[0]
            ));
        }

        return $this->render('app/version.html.twig', array(
            'lastVersion' => null
        ));
    }

    public function getVersions(){
        $jsonFile = "assets/version.json";
        if(file_exists($jsonFile)){
            $json = file_get_contents($jsonFile, false);
            $jsonDatas = json_decode($json, true);

            return $jsonDatas;
        }
        return null;
    }
}