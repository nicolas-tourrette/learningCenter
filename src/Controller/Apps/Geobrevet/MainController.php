<?php

// src/Controller/MainController.php

namespace App\Controller\Apps\Geobrevet;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\Test;

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
            $json = new Json();
            throw new NotFoundHttpException($json->throwErrorMessage("e3001"));
        }

        $json = new Json("assets/datas/geobrevet/".$discipline."/".$content.".json");
        $content = $json->parseJson();

        if(is_string($content)){
            throw new NotFoundHttpException($json->throwErrorMessage("e3002"));
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
            $json = new Json();
            throw new NotFoundHttpException($json->throwErrorMessage("e3001"));
        }

        $variante = array("a");
        $keyVariante = array_rand($variante, 1);

        $json = new Json("assets/datas/geobrevet/test/".$id.$variante[$keyVariante].".json");
        $content = $json->parseJson();

        if(is_string($content)){
            throw new NotFoundHttpException($json->throwErrorMessage("e3003"));
        }

        $form = $this->createFormBuilder();
        $form->add('test', HiddenType::class, array(
            'data' => $id.$variante[$keyVariante]
        ));
        
        shuffle($content["questions"]);
        foreach($content["questions"] as $question){
            $choices = array();
            for($i=0 ; $i<count($question["reponses"]); $i++) {
                foreach($question["reponses"][$i] as $key => $value){
                    $choice = array($value => $key);
                    array_push($choices, $choice);
                }
            }
            $form->add($question["id"],
                ChoiceType::class,
                array(
                    'label' => $question["question"],
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                    'choice_attr' => function($choice, $key, $value) {
                        return ['class' => "form-check-input"];
                    },
                    'attr' => ['class' => "form-check"]
                )
            );
        }
            
        $form = $form->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
			if($form->isSubmitted() && $form->isValid()) {
                $testId = $form->getData()["test"];
                $json = new Json("assets/datas/geobrevet/test/".$testId.".json");
                $content = $json->parseJson();

                if(is_string($content)){
                    throw new NotFoundHttpException($json->throwErrorMessage("e3003"));
                }

                $points = 0;
                $total = count($content["questions"]);

                foreach($content["questions"] as $question){
                    if($question["correction"] == $form->getData()[$question["id"]]){
                        $points++;
                    }
                }
                $score = $points/$total*100;

                $test = new Test();
                $test->setUser($this->getUser());
                $test->setApp($appDetails);
                $test->setScore($score);
                $test->setTest($testId);

                $em->persist($test);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Votre résultat au test '.strtoupper($testId).' a été enregistré ! Votre score est de '.$score.' %.');
                return $this->redirectToRoute('geobrevet_index');
            }
        }

        return $this->render('app/geobrevet/test-'.$content["discipline"].'.html.twig', array(
            'appDetails' => $appDetails,
            'content' => $content,
            'form' => $form->createView()
        ));
    }
}