<?php

namespace App\Form;

use App\Entity\Programme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddProgrammeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Titre',
                ]
            ])
            ->add('description', TextareaType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Description',
                ]
            ])
            ->add('image', filetype::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                ]
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Perte du poids' => 'Perte du poids',
                    'Yoga' => 'Yoga',
                    'Cardio' => 'Cardio',
                    'Nutrition' => 'Nutrition',
                    'Musculation' => 'Musculation',
                ],
                'attr' => [
                    'placeholder' => 'Categorie',
                ]
            ])
            ->add('contenu', CKEditorType::class,[
                'label' => false,
                'attr' => [
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Programme::class,
        ]);
    }
}
