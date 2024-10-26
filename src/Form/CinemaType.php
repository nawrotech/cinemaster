<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Form\VisualFormatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
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
            ->add(
                "maxRows",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" => "How many rows has biggest screening room in your cinema?",
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),
                    ],

                ]
            )
            ->add(
                "maxSeatsPerRow",
                NumberType::class,
                [
                    "attr" => [
                        "placeholder" => "e.g. 6"
                    ],
                    "label" => "How many seats has the longest row in your cinema?",
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(1),

                    ]
                ]
            )
            ->add("streetName")
            ->add("buildingNumber")
            ->add("postalCode")
            ->add("city")
            ->add("district")
            ->add("country")
            ->add('visualFormats', CollectionType::class, [
                "label" => false,
                "entry_type" => VisualFormatType::class,
                "entry_options" => ["label" => false],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
                "prototype_name" => "__visual_format_name__"
                
            ])
            ->add('save', SubmitType::class, [
                "attr" => [
                    "class" => "btn-success",

                ]
            ]);
   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
        ]);
    }
}
