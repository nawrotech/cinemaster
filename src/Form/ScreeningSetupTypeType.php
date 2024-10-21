<?php

namespace App\Form;

use App\Entity\ScreeningSetupType;
use App\Entity\VisualFormat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreeningSetupTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('soundFormat', TextType::class)
            ->add('visualFormat', EntityType::class, [
                'class' => VisualFormat::class,
                'choice_label' => 'name',
            ])
            ->add("remove", ButtonType::class, [
                "attr" => [
                    "data-form-collection-target" => "removeButton",
                    "data-action" => "click->form-collection#removeElement"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningSetupType::class,
        ]);
    }
}
