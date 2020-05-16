<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',
                TextType::class,
                array(
                    'label' => "Nom d'utilisateur",
                    'required' => true,
                    'attr' => ["placeholder" => "prenom.nom", 'class' => "form-control"]
                )
            )
            ->add('password',
            RepeatedType::class,
                array(
                    'type' => PasswordType::class,
                    'label' => "Mot de passe",
                    'required' => true,
                    'options' => ['attr' => ['class' => "password-field"]],
                    'invalid_message' => 'Les  mots de passe doivent correspondre.',
                    'first_options'  => ['label' => 'Mot de passe', 'attr' => [ 'placeholder' => "Mot de passe", 'class' => "form-control", 'minlength' => "8" ]],
                    'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['placeholder' => "Confirmation du mot de passe", 'class' => "form-control", 'minlength' => "8" ]],
                    'attr' => [ 'class' => "form-control" ]
                )
            )
            ->add('name',
                TextType::class,
                array(
                    'label' => "Prénom & NOM",
                    'required' => true,
                    'attr' => ["placeholder" => "Prénom NOM", 'class' => "form-control"]
                )
            )
            ->add('birthday',
                DateType::class,
                array(
                    'label' => "Date de naissance",
                    'required' => true,
                    'widget' => 'single_text',
                    'attr' => ['class' => "form-control"]
                )
            )
            ->add('email',
                EmailType::class,
                array(
                    'label' => "Adresse e-mail",
                    'required' => true,
                    'attr' => ["placeholder" => "prenom.nom@fournisseur.fr", 'class' => "form-control"]
                )
            )
            ->add('school',
                TextType::class,
                array(
                    'label' => "Établissement scolaire/universitaire",
                    'required' => false,
                    'attr' => ["placeholder" => "Collège du Parc - Dijon", 'class' => "form-control"]
                )
            )
            ->add('profilImage',
                TextType::class,
                array(
                    'label' => "URL de photo de profil",
                    'required' => false,
                    'attr' => ["placeholder" => "https://facebook.com/myname/photos/profile", 'class' => "form-control"],
                    'empty_data' => "assets/images/avatars/user.svg"
                )
            )
            ->add('partnerSchool',
                TextType::class,
                array(
                    'label' => "Code établissement partenaire (RNE)",
                    'required' => false,
                    'attr' => ["placeholder" => "0211234C", 'class' => "form-control", 'maxlength' => "8"]
                )
            )
            ->add('roles',
                ChoiceType::class,
                array(
                    'label' => "Souscription au plan",
                    'required' => true,
                    'attr' => ['class' => "custom-select"],
                    'multiple'=> true,
                    'expanded'=> false,
                    'choices' => array(
                        'LearnApp Classic (gratuit)' => "ROLE_USER",
                        'LearnApp Classic+ (5 €/mois)' => "ROLE_USER-PLUS",
                        'LearnApp Premium (10 €/mois)' => "ROLE_USER-PREMIUM"
                    )
                )
            )
            ->add('handicap',
                CheckboxType::class,
                array(
                    'label' => "J'ai un handicap scolaire.",
                    'required' => false,
                    'attr' => ['class' => "custom-control-input"]
                )
            )
            ->add('submit',
                SubmitType::class,
                array(
                    'label' => "Valider la création du compte",
                    'attr' => ['class' => "btn btn-primary"]
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
