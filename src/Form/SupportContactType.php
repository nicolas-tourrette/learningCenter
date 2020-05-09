<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',
                TextType::class,
                array(
                    'label' => "Nom",
                    'required' => true,
                    'attr' => ["placeholder" => "Jean DUPONT", 'class' => "form-control"]
                )
            )
            ->add('email',
                EmailType::class,
                array(
                    'label' => "E-Mail de contact",
                    'required' => true,
                    'attr' => ["placeholder" => "prenom.nom@fournisseur.fr", 'class' => "form-control"]
                )
            )
            ->add('function',
                ChoiceType::class,
                array(
                    'label' => "Votre fonction",
                    'required' => true,
                    'attr' => ['class' => "custom-select"],
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => array(
                        'Indépendant' => 'Indépendant',
                        'Élève/étudiant' => 'Élève/étudiant',
                        'Professeur' => 'Professeur',
                        'Chef d\'établissement' => 'Chef d\'établissement'
                    )
                )
            )
            ->add('school',
                TextType::class,
                array(
                    'label' => "Votre établissement",
                    'required' => false,
                    'attr' => ["placeholder" => "Nom de l'établissement", 'class' => "form-control"]
                )
            )
            ->add('subject',
                TextType::class,
                array(
                    'label' => "Objet du message",
                    'required' => true,
                    'attr' => ["placeholder" => "L'objet de votre message", 'class' => "form-control"]
                )
            )
            ->add('message',
                TextareaType::class,
                array(
                    'label' => "Votre message",
                    'required' => true,
                    'attr' => ["placeholder" => "Votre message ici", 'class' => "form-control"]
                )
            )
            ->add('submit',
                SubmitType::class,
                array(
                    'label' => "Envoyer votre message",
                    'attr' => ['class' => "btn btn-primary"]
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
