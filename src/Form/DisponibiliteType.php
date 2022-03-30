<?php

namespace App\Form;

use App\Entity\Disponibilite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisponibiliteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('canal', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    '' => '',
                    'Whatsupp' => 'Whatsupp',
                    'Zoom' => 'Zoom',
                    'Teams' => 'Teams'
                ]
            ])
            ->add('duree', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    '' => '',
                    '30min' => '30min',
                    '1h' => '1h',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Disponibilite::class,
        ]);
    }
}
