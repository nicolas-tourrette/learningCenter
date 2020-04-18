<?php

// src/Controller/MainController.php

namespace App\Controller\Apps\Geobrevet;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

use App\Service\JsonParser as Json;

/**
 * @Route("/me/apps/geobrevet")
 */
class MainController extends AbstractController {
    
    /**
     * @Route("/dashboard", name="geobrevet_index")
     */
    public function index(Request $request){
        
        $request->getSession()->getFlashBag()->add('warning', "TODO");
        return $this->render('app/about.html.twig');
    }

    /**
     * @Route("/{discipline}/{content}", name="geobrevet_learn", requirements={"discipline": "^(?!test).+"})
     */
    public function learn(Request $request, $discipline, $content){
        $em = $this->getDoctrine()->getManager();
        $appDetails = $em->getRepository("App:App")->findOneByAppCode("geobrevet");

        if($appDetails == null){
            throw new NotFoundHttpException("Error code #2000 — Application inconnue.");
        }

        $json = new Json("assets/datas/geobrevet/".$discipline."/".$content.".json");
        $content = $json->parseJson();

        if($content == null){
            throw new NotFoundHttpException("Error code #2001 — Contenu de l'application inacessible.");
        }

        return $this->render('app/geobrevet/content-'.$discipline.'.html.twig', array(
            'appDetails' => $appDetails,
            'content' => $content
        ));
    }

    /**
     * @Route("/test/{id}", name="geobrevet_test")
     */
    public function test(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $appDetails = $em->getRepository("App:App")->findOneByAppCode("geobrevet");

        if($appDetails == null){
            throw new NotFoundHttpException("Error code #2000 — Application inconnue.");
        }

        $json = new Json("assets/datas/geobrevet/test/".$id.".json");
        $content = $json->parseJson();

        if($content == null){
            throw new NotFoundHttpException("Error code #2001 — Contenu de l'application inacessible.");
        }

        return $this->render('app/geobrevet/test-'.$discipline.'.html.twig', array(
            'appDetails' => $appDetails,
            
        ));
    }
}