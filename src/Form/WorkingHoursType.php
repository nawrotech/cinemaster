<?php

namespace App\Form;

use App\Entity\WorkingHours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkingHoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('openTime', null, [
                'widget' => 'single_text',
            ])
            ->add('closeTime', null, [
                'widget' => 'single_text',
            ])
            ->add('dayOfTheWeek', ChoiceType::class, [
                'choices' => [
                    "Monday" => 0,
                    "Tuesday" => 1,
                    "Wednesday" => 2,
                    "Thursday" => 3,
                    "Friday" => 4,
                    "Saturday" => 5,
                    "Sunday" => 6,
                ],
                "disabled" => true
            ])     
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkingHours::class,
        ]);
    }
}
