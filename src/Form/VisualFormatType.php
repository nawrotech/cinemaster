<?php

namespace App\Form;

use App\Entity\VisualFormat;
use App\Form\Type\RemoveButtonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VisualFormatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $visualFormat = $event->getData();
                $form = $event->getForm();

                if ($visualFormat?->getId()) {
                    $form->add("name", TextType::class, [
                        "attr" => [
                            "readonly" => true
                        ]
                    ]);
                }
            })
            ->add("name", TextType::class, [
                "label" => "Visual format",
                "attr" => [
                    "placeholder" => "eg. IMAX, 3D",
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2, max: 40, minMessage: 'Name must be at least {{ limit }} characters', maxMessage: 'Name cannot be longer than {{ limit }} characters')
                ]
            ])
            ->add("remove", RemoveButtonType::class)
      
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisualFormat::class,
        ]);
    }
}
