<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeatLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('row', ChoiceType::class, [
                'choices' => $options["allowed_rows"],
                "choice_label" => function ($choice, $key, $value) {
                    return $value;
                },
                "choice_value" => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Choose a row',
                'required' => true,
            ])
            ->add("seat_type", ChoiceType::class, [
                'choices' => array_combine($options['allowed_seat_types'], $options['allowed_seat_types']),
                'placeholder' => 'Choose a seat type for the entire row',
                'required' => true,
            ])
            ->add("col_start", NumberType::class)
            ->add("col_end", NumberType::class)
            ->add("submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            "data_class" => null,
            "allowed_rows" => [],
            "allowed_seat_types" => ["Regular", "Handicapped", "5D"] // static table with those
        ]);
    }
}
