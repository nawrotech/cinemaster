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
        $builder
            ->add('name', TextType::class)
            ->add("screening_room_size", ScreeningRoomSizeType::class, [
                "label" => false,
                'mapped' => false,
                "max_rows_label" => "What is the number of rows in your room?",
                "max_columns_label" =>  "What is the number of columns in your room?",
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningRoom::class,
        ]);
    }
}
