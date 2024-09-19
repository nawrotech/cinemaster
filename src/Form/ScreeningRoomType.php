<?php

namespace App\Form;

use App\Entity\ScreeningRoom;
use App\Form\Type\ScreeningRoomSizeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreeningRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        [
            "maxColNum" => $maxColNum,
            "maxRowNum" => $maxRowNum
        ] = $options["max_room_sizes"];

        $builder
            ->add('name', TextType::class, [
                "label" => "Screening room name",
                "attr" => [
                    "placeholder" => "e.g. Room A"
                ]
            ])
            ->add("screening_room_size", ScreeningRoomSizeType::class, [
                "label" => false,
                'mapped' => false,
                "max_row_label" => "What is the number of rows in your room?",
                "max_column_label" =>  "What is the number of seats in one row in your room?",
                "max_column_constraints" => ["upper_limit" => $maxColNum],
                "max_row_constraints" => ["upper_limit" => $maxRowNum],

            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningRoom::class,
            "max_room_sizes" => null
        ]);
    }
}
