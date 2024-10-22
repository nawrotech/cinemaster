<?php

namespace App\Form;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSetup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            "maxRows" => $maxRowsConstraint,
            "maxSeatsPerRow" => $maxSeatsPerRowConstraint
        ] = $options["max_room_sizes"];

        $cinema = $options["data"]->getCinema();

        $builder
            ->add('name', TextType::class, [
                "label" => "Screening room name",
                "attr" => [
                    "placeholder" => "e.g. Room A"
                ]
            ])
            ->add("maxRows", NumberType::class, [
                "label" => "Specify number of rows in your room",
                "mapped" => false,
                'constraints' => [
                    new NotBlank(),
                    new  Positive(),
                    new LessThanOrEqual([
                        'value' => $maxRowsConstraint,  
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add("maxSeatsPerRow", NumberType::class, [
                "label" => "Seats per row default",
                "mapped" => false,
                "constraints" => [
                    new NotBlank(),
                    new  Positive(),
                    new LessThanOrEqual([
                        'value' => $maxSeatsPerRowConstraint,  
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add('seatsPerRow', CollectionType::class, [
                'entry_type' => NumberType::class,
                'entry_options' => [
                    'label' => false,
                    'constraints' => [
                        new NotBlank(),
                        new Positive(),
                        new LessThanOrEqual([
                            'value' => $maxSeatsPerRowConstraint,  
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
            ->add("screeningRoomSetup", EntityType::class, [
                "class" => ScreeningRoomSetup::class,
                "query_builder" => function(EntityRepository $er) use($cinema): QueryBuilder  {
                    return $er->createQueryBuilder("srs")
                            ->where("srs.cinema = :cinema")
                            ->setParameter("cinema", $cinema);
                },
                'choice_label' => 'displaySetup',

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
