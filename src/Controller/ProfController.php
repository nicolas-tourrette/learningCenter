<?php

// src/Controller/ProfController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

use App\Service\JsonParser as Json;

/**
 * @Route("/teacher")
 */
class ProfController extends AbstractController {
    /**
     * @Route("", name="profIndex")
     */
    public function profIndex(){
        return $this->render("app/teacher/dashboard.html.twig");
    }

    /**
     * @Route("/class/{id}", name="profClass", requirements={"id" = "\d+"})
     */
    public function profClass($id){
        $em = $this->getDoctrine()->getManager();
        $class = $em->getRepository("App:SchoolClass")->find($id);
        $students = $em->getRepository("App:User")->findBySchoolClass($id);

        return $this->render("app/teacher/class.html.twig", array(
            'class' => $class,
            'students' => $students
        ));
    }
}