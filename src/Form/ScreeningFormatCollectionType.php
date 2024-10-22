<?php

namespace App\Form;

use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ScreeningFormatCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
           ->add("screeningRoomSetups", CollectionType::class, [
                "label" => false,
                "entry_type" => ScreeningFormatType::class,
                "entry_options" => [
                    "label" => false,
                    "language_presentation_choices" => $options["language_presentation_choices"]
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
                // "prototype_name" => "__name__"
           ])
           ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
            "language_presentation_choices" => null
        ]);
    }
}
