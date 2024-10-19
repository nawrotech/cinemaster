<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $cart = $options["cart"];

        $builder
            ->add("email", EmailType::class, [
                "required" => true,

                // "constraints" => "required"
            ])
            ->add("submit", SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, function(PostSubmitEvent $event) use($cart) {
                $form = $event->getForm();
                if (!$cart) {
                    $form->addError(new FormError("At least one seat must be selected!"));
                }
            })

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "cart" => null
        ]);
    }
}
