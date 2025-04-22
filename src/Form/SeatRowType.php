<?php

namespace App\Form;

use App\Enum\ScreeningRoomSeatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeatRowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        $allowedRows = $options["allowed_rows"];

        $builder
            ->add('row', ChoiceType::class, [
                'choices' => $allowedRows,
                "choice_label" => function ($choice, $key, $value) {
                    return $value;
                },
                "choice_value" => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Choose a row',
                'required' => true,
            ])
            ->add("seatType", EnumType::class, [
                "class" => ScreeningRoomSeatType::class,
                'choice_label' => fn(ScreeningRoomSeatType $screeningRoomSeatType) => $screeningRoomSeatType->value,
                'placeholder' => 'Choose a seat type for the entire row',
                'required' => true,
            ])
            ->add("firstSeatInRow", IntegerType::class, [
                "data" => 1
            ])
            ->add("lastSeatInRow", IntegerType::class, [
           
            ])
            ->add("submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "allowed_rows" => [],
        ]);
    }
}
