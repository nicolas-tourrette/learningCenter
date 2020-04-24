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
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        $testsHistoire = $em->getRepository("App:Test")->findTestByUserAndAppAndDiscipline($user, "geobrevet", "1");
        $testsGeographie = $em->getRepository("App:Test")->findTestByUserAndAppAndDiscipline($user, "geobrevet", "2");

        $histoire = $this->processResults($em, $user, $testsHistoire);
        $geographie = $this->processResults($em, $user, $testsGeographie);

        $json = new Json("assets/datas/geobrevet/infos.json");
        $infos = $json->parseJson();

        return $this->render('app/geobrevet/dashboard.html.twig', array(
            "histoire" => array(
                'labels' => substr($histoire["labels"],0,-1)."]",
                'datas' => $histoire["datas"],
                'resultats' => $histoire["resultats"],
            ),
            "geographie" => array(
                'labels' => substr($geographie["labels"],0,-1)."]",
                'datas' => $geographie["datas"],
                'resultats' => $geographie["resultats"],
            ),
            'infos' => $infos
        ));
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
            if ($request->isMethod('POST')) {
                throw new NotFoundHttpException($json->throwErrorMessage("e3004"));
            }
            else{
                throw new NotFoundHttpException($json->throwErrorMessage("e3003"));
            }
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
                    'required' => true
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

        return $this->render('app/geobrevet/test.html.twig', array(
            'appDetails' => $appDetails,
            'content' => $content,
            'form' => $form->createView()
        ));
    }


    private function processResults($em, $user, $tests){
        $colors = array("#ff6384", "#36a2eb", "#cc65fe", "#ffce56");
        $testsParsed = array();
        $datas = "";
        $datasDisplay = "[";

        $max = 0;
        for($i=0; $i < count($tests); $i++){
            $testId = $tests[$i]->getTest();
            $globalTestId = substr($testId,0,-1);
            if(!in_array($globalTestId, $testsParsed)){
                array_push($testsParsed, $globalTestId);
                $scores = array();
                $scroresDisplay = array();
                $thisTestScores = array_reverse($em->getRepository("App:Test")->findTestByUserAndTest($user, $globalTestId));
                foreach($thisTestScores as $test){
                    $date = $test->getDate()->format("d/m/Y H:i:s");
                    $score = $test->getScore();

                    array_push($scores, $score);

                    $grade = "-";
                    $class = "grade-none";
                    if($score >= 90){ $grade = "A"; $class = "grade-a"; }
                    elseif($score >= 75 && $score < 90){ $grade = "B"; $class = "grade-b"; }
                    elseif($score >= 60 && $score < 75){ $grade = "C"; $class = "grade-c"; }
                    elseif($score >= 40 && $score < 60){ $grade = "D"; $class = "grade-d"; }
                    elseif($score >= 25 && $score < 40){ $grade = "E"; $class = "grade-e"; }
                    elseif($score < 25){ $grade = "F"; $class = "grade-f"; }

                    $resultat = array("date" => $date, "result" => array("score" => $score, "grade" => $grade, "class" => $class));
                    array_push($scroresDisplay, $resultat);
                }
                if(count($scores) > $max){
                    $max = count($scores);
                }
                
                $color = array_rand($colors);
                
                $datas .= json_encode(array("label" => "Test ".$globalTestId, "borderColor" => $colors[$color], "data" => $scores)).",";
                $datasDisplay .= json_encode(array("test" => "Test ".$globalTestId, "resultats" => $scroresDisplay)).",";
                unset($colors[$color]);
            }
        }
        $datas = substr($datas,0,-1);
        $datasDisplay = substr($datasDisplay,0,-1)."]";
        $resultats = json_decode($datasDisplay, true);
        if($resultats != null){
            sort($resultats);
        }
        
        $labels = "[";
        for($i=1; $i <= $max; $i++){
            $labels .= $i.",";
        }

        return array("labels" => $labels, "datas" => $datas, "resultats" => $resultats);
    }
}