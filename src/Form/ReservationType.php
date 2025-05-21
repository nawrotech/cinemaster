<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $selectedSeats = $options["selectedSeats"];

        $builder
            ->add("email", EmailType::class, [
                "required" => true,
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2, max: 30)
                ]
            ])
            ->add("proceedToCheckout", SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, function(PostSubmitEvent $event) use($selectedSeats) {
                $form = $event->getForm();
                if (empty($selectedSeats)) {
                    $form->addError(new FormError("At least one seat must be selected!"));
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "selectedSeats" => null
        ]);
    }
}
