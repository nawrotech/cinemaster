<?php

namespace App\Form\Type;

use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CinemaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add(
                "name",
                TextType::class,
                [
                    "label" => "Enter cinema name",
                    "attr" => [
                        "placeholder" => "Enter cinema name"
                    ],
                    "constraints" => [
                        new NotBlank()
                    ]
                ]
            )
            ->add("screening_room_size", ScreeningRoomSizeType::class, [
                "label" => false,
                'mapped' => false,
                "max_row_label" => "What is the biggest number of rows your screening room has in cinema?",
                "max_column_label" => "What is the biggest number of seats in one row in your cinema?"

            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
        ]);
    }
}
