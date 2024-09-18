<?php

namespace App\Form\Type;

use App\Entity\Cinema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class CinemaBiggestScreeningRoomSizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                "name",
                TextType::class,
                [
                    "label" => "Enter cinema name",
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank()
                    ]
                ]
            )
            ->add(
                "max_rows",
                NumberType::class,
                [
                    "label" => "Enter rows number of biggest room in the cinema",
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(5)
                    ]
                ]
            )
            ->add(
                "max_columns",
                NumberType::class,
                [
                    "label" => "Enter biggest number of seats in single row",
                    "mapped" => false,
                    "constraints" => [
                        new NotBlank(),
                        new GreaterThan(5)
                    ]
                ]
            )
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
