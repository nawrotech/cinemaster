<?php

namespace App\Form;

use App\Entity\PriceTier;
use App\Enum\ScreeningRoomSeatType;
use App\Repository\PriceTierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeatRowType extends AbstractType
{
    public function __construct(
        private PriceTierRepository $priceTierRepository
        )
    {   
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        $allowedRows = $options["allowed_rows"];
        $cinema = $options['cinema'];


        $builder
            ->add('rowStart', ChoiceType::class, [
                'choices' => $allowedRows,
                "choice_label" => function ($choice, $key, $value) {
                    return $value;
                },
                "choice_value" => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Choose a row',
                'required' => true,
            ])
            ->add('rowEnd', ChoiceType::class, [
                'choices' => $allowedRows,
                "choice_label" => function ($choice, $key, $value) {
                    return $value;
                },
                "choice_value" => function ($choice) {
                    return $choice;
                },
                'placeholder' => 'Choose a row',
                'required' => true,
            ])
            ->add('priceTier', EntityType::class, [
                'class' => PriceTier::class,    
                'choices' => $this->priceTierRepository->findByCinemaAndActiveStatus($cinema),
                'choice_label' => function (PriceTier $priceTier) {
                    return sprintf('%s ($%.2f)', $priceTier->getName(), $priceTier->getPrice());
                },
                'mapped' => false,
            ])
            ->add("seatType", EnumType::class, [
                "class" => ScreeningRoomSeatType::class,
                'choice_label' => fn(ScreeningRoomSeatType $screeningRoomSeatType) => $screeningRoomSeatType->value,
                'placeholder' => 'Choose a seat type for the entire row',
                'required' => true,
            ])
            ->add("firstSeatInRow", IntegerType::class, [
                "data" => 1
            ])
            ->add("lastSeatInRow", IntegerType::class, [
            ])
            ->add("submit", SubmitType::class)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                
                if (!empty($data['rowStart']) && !empty($data['rowEnd']) && $data['rowStart'] > $data['rowEnd']) {
                    $form->get('rowEnd')->addError(new FormError('End row must be greater than or equal to start row'));
                }

                if (!empty($data['firstSeatInRow']) && !empty($data['lastSeatInRow']) && $data['firstSeatInRow'] > $data['lastSeatInRow']) {
                    $form->get('lastSeatInRow')->addError(new FormError('Last seat in row must be greater than or equal first seat in row'));
                }

            });
        ;

   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "allowed_rows" => [],
            'cinema' => null
        ]);
    }
}
