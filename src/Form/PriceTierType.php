<?php

namespace App\Form;

use App\Entity\PriceTier;
use App\Form\Type\RemoveButtonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PriceTierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Tier Name',
                'attr' => ['placeholder' => 'e.g. Premium, VIP, Standard'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'USD',
                'attr' => ['placeholder' => '10.00'],
            ])
            ->add('color', ColorType::class, [
                'label' => 'Pick a color'
            ])
            ->add("remove", RemoveButtonType::class)
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceTier::class,
        ]);
    }
}