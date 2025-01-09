<?php

namespace App\Form;

use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CinemaScreeningFormatCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
           ->add("screeningFormats", CollectionType::class, [
                "data" => $options["active_screening_formats"],
                "label" => false,
                "entry_type" => ScreeningFormatType::class,
                "entry_options" => [
                    "label" => false,
                    "cinema" => $options["data"]
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
           ])
           ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
            "active_screening_formats" => null
        ]);
    }
}
