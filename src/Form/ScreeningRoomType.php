<?php

namespace App\Form;

use App\Entity\PriceTier;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSetup;
use App\Repository\PriceTierRepository;
use App\Repository\ScreeningRoomSetupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ScreeningRoomType extends AbstractType
{
    public function __construct(
        private ScreeningRoomSetupRepository $screeningRoomSetupRepository,
        private PriceTierRepository $priceTierRepository
        )
    {   
    }

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
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2, max: 50)
                ]
            ])
            ->add("maxRows", IntegerType::class, [
                "label" => "Specify number of rows in your room",
                "mapped" => false,
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                    new LessThanOrEqual([
                        'value' => $maxRowsConstraint,  
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add("maxSeatsPerRow", IntegerType::class, [
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
                "label" => false,
                'entry_type' => IntegerType::class,
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
                "by_reference" => false,
                'prototype_name' => '__seats_per_row__',
            ])
            ->add("maintenanceTimeInMinutes", IntegerType::class, [
                "constraints" => [
                    new NotBlank(),
                    new LessThanOrEqual([
                        'value' => 60,  
                        'message' => 'The maximum number of seats per row is {{ compared_value }}.'
                    ])
                ]
            ])
            ->add("screeningRoomSetup", EntityType::class, [
                "class" => ScreeningRoomSetup::class,
                "choices" => $this->screeningRoomSetupRepository->findByCinemaAndActiveStatus($cinema, true),
                'choice_label' => 'displaySetup',
            ])
            ->add('priceTier', EntityType::class, [
                'class' => PriceTier::class,    
                'choices' => $this->priceTierRepository->findByCinemaAndActiveStatus($cinema),
                'choice_label' => function (PriceTier $priceTier) {
                    return sprintf('%s ($%.2f)', 
                            ucfirst($priceTier->getType()->value),
                            $priceTier->getPrice());
                },
                'mapped' => false,
                'label' => 'What\'s the base price for cinema seats?'
            ])
            ->add("create", SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $seatsPerRow = $form->get('seatsPerRow')->getData();
                if (empty($seatsPerRow)) {
                    $form->get('seatsPerRow')->addError(new FormError(
                        'Please generate rows before submitting the form'
                    ));
                }
            });
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
