<?php

namespace App\Form;

use App\Entity\PriceTier;
use App\Enum\SeatPricing;
use App\Form\Type\RemoveButtonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PriceTierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => SeatPricing::class,
                'label' => 'Tier type',
                'choice_label' => fn (SeatPricing $seatPricing): string => ucfirst($seatPricing->value)
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