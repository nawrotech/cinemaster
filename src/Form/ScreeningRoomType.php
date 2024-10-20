<?php

namespace App\Form;

use App\Entity\ScreeningRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ScreeningRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        [
            "maxRows" => $maxRows,
            "maxSeatsPerRow" => $maxSeatsPerRow
        ] = $options["max_room_sizes"];

        $builder
            ->add('name', TextType::class, [
                "label" => "Screening room name",
                "attr" => [
                    "placeholder" => "e.g. Room A"
                ]
            ])
            ->add("rowsMax", NumberType::class, [
                // "data" => 4,
                "label" => "Specify number of rows in your room",
                "mapped" => false,
                'constraints' => [
                    new NotBlank(),
                    new  Positive(),
                    new LessThanOrEqual([
                        'value' => $maxSeatsPerRow,  // Adjust this value as needed
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add("seatsPerRowMax", NumberType::class, [
                "label" => "Seats per row default",
                "mapped" => false,
                "constraints" => [
                    new NotBlank(),
                    new  Positive(),
                    new LessThanOrEqual([
                        'value' => $maxRows,  // Adjust this value as needed
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add('seatsPerRow', CollectionType::class, [
                // "data" => [6, 6, 6, 8],
                'entry_type' => NumberType::class,
                'entry_options' => [
                    'label' => false,
                    'constraints' => [
                        new NotBlank(),
                        new Positive(),
                        new LessThanOrEqual([
                            'value' => $maxRows,
                            'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                        ])
                    ]
                ],
                "mapped" => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__seats_per_row__',
            ])
            ->add("maintenanceTimeInMinutes", NumberType::class, [
                "constraints" => [
                    new NotBlank(),
                    new Positive()
                ]
            ])
            ->add("apply", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ScreeningRoom::class,
            "max_room_sizes" => null,

        ]);
    }
}
