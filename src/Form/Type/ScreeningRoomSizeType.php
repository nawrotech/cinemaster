<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScreeningRoomSizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxRowsLabel = $options["max_rows_label"];
        $maxColumnsLabel = $options["max_columns_label"];


        $builder
            ->add(
                "max_rows",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "Enter number of rows e.g. 6"
                    ],
                    "label" => $maxRowsLabel,
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1)
                    ]
                ]
            )
            ->add(
                "max_columns",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "Enter number of columns e.g. 6"
                    ],
                    "label" => $maxColumnsLabel,
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1)
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            "max_rows_label" => null,
            "max_columns_label" => null
        ]);
    }
}
