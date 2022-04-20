<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\CallbackTransformer;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'input--style-2',
                    'placeholder' => 'Nom',
                ]
            ])
            ->add('prenom', textType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'input--style-2',
                    'placeholder' => 'Prenom',
                ], 
                
            ])
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'name' => 'class',
                    'placeholder' => 'Vous etes',
                ],
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'Client' => "ROLE_CLIENT",
                    'Coach' => "ROLE_COACH"
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'input--style-2',
                    'placeholder' => 'Email',
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'input--style-2',
                    'placeholder' => 'Mot de passe',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
        // Data transformer
        $builder->get('roles')
        ->addModelTransformer(new CallbackTransformer(
            function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray)? $rolesArray[0]: null;
            },
            function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
            }
    ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
