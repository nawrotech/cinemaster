<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScreeningRoomSizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $maxRowUpperLimit = $options["max_rows_constraints"]["upper_limit"] ?? 25;
        $maxColumnUpperLimit = $options["max_seats_per_row_constraints"]["upper_limit"] ?? 25;

        $builder
            ->add(
                "maxRows",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" =>  $options["max_rows_label"],
                    "data" => $options["max_rows_default"] ?? null,

                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),
                        new LessThanOrEqual($maxRowUpperLimit)
                    ],

                ]
            )
            ->add(
                "maxSeatsPerRow",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" => $options["max_seats_per_row_label"],
                    "data" => $options["max_seats_per_row_default"] ?? null,
                   
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),
                        new LessThanOrEqual((int) $maxColumnUpperLimit)
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "max_rows_label" => null,
            "max_seats_per_row_label" => null,
            "max_rows_constraints" => null,
            "max_seats_per_row_constraints" => null,
            "max_rows_default" => null,
            "max_seats_per_row_default" => null
        ]);
    }
}
