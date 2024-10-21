<?php

namespace App\Form;

use App\Entity\VisualFormat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisualFormatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("name", TextType::class, [
                "label" => "Visual format",
                "attr" => [
                    "placeholder" => "eg. IMAX, 3D",
                ],
            ])
            ->add("remove", ButtonType::class, [
                "attr" => [
                    "data-form-visual-format-collection-target" => "removeButton",
                    "data-action" => "click->form-visual-format-collection#removeElement"
                ]
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisualFormat::class,
        ]);
    }
}
