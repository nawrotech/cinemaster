<?php

namespace App\Form\Type;

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
        $maxRowLabel = $options["max_row_label"];
        $maxColumnLabel = $options["max_column_label"];

        $maxRowUpperLimit = $options["max_row_constraints"]["upper_limit"] ?? 100;
        $maxColumnUpperLimit = $options["max_column_constraints"]["upper_limit"] ?? 100;

        $builder
            ->add(
                "max_row",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" => $maxRowLabel,
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),
                        new LessThanOrEqual($maxRowUpperLimit)
                    ]
                ]
            )
            ->add(
                "max_column",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" => $maxColumnLabel,
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),
                        new LessThanOrEqual($maxColumnUpperLimit)
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            "max_row_label" => null,
            "max_column_label" => null,
            "max_row_constraints" => null,
            "max_column_constraints" => null,
        ]);
    }
}
