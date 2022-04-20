<?php

namespace App\Form;

use App\Entity\Disponibilite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'date_widget' => 'single_text',
                'attr' => [
                    'class' => 'datetime-inputs'
                ]
            ])
            ->add('duree', ChoiceType::class, [
                'choices' => ['30 Min' => "30", '1 H' => "60", '1 H 30 Min' => '90'],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('canal', ChoiceType::class, [
                'choices' => ['Zoom' => "Zoom", 'Skype' => "Skype", 'Google Meet' => 'Google Meet', 'Microsoft Teams' => "Microsoft Teams"],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Disponibilite::class,
        ]);
    }
}
