<?php

namespace App\Form\Type;

use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CinemaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $cinema = $options["data"];


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
            ->add("screeningRoomSize", ScreeningRoomSizeType::class, [
                "label" => false,
                'mapped' => false,
                "max_rows_label" => "What is the biggest number of rows your screening room has in cinema?",
                "max_seats_per_row_label" => "What is the biggest number of seats in one row in your cinema?",
                "max_rows_default" => $cinema->getMaxRows() ?? null,
                "max_seats_per_row_default" => $cinema->getMaxSeatsPerRow() ?? null
            ])
            ->add('save', SubmitType::class, [
                "attr" => [
                    "class" => "btn-success",

                ]
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (PostSubmitEvent $event) {
                $form = $event->getForm();
                $cinema = $event->getData();

                $screeningRoomSize = $form->get("screeningRoomSize");
                $maxRows = $screeningRoomSize->get("maxRows")->getData();
                $maxSeatsPerRow = $screeningRoomSize->get("maxSeatsPerRow")->getData();

                $cinema->setMaxRows($maxRows);
                $cinema->setMaxSeatsPerRow($maxSeatsPerRow);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
        ]);
    }
}
