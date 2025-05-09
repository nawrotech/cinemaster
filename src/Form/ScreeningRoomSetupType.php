<?php

namespace App\Form;

use App\Entity\ScreeningRoomSetup;
use App\Entity\VisualFormat;
use App\Form\Type\RemoveButtonType;
use App\Repository\VisualFormatRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ScreeningRoomSetupType extends AbstractType
{

    public function __construct(private VisualFormatRepository $visualFormatRepository)
    {
        $this->visualFormatRepository = $visualFormatRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cinema = $options["query_constraint"];

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $screeningRoomSetup = $event->getData();
                $form = $event->getForm();

                if ($screeningRoomSetup?->getId()) {
                    $form->add("soundFormat", TextType::class, [
                        "mapped" => false,
                        "data" => $screeningRoomSetup->getSoundFormat(),
                        "attr" => [
                            "readonly" => true
                        ],
                    ]);
                    $form->add("visualFormat", TextType::class, [
                        "mapped" => false,
                        "data" => $screeningRoomSetup->getVisualFormat()->getName(),
                        "attr" => [
                            "readonly" => true
                        ],
                    ]);
                }
            })
            ->add('soundFormat', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2,max: 50)
                ],
            ])
            ->add('visualFormat', EntityType::class, [
                'class' => VisualFormat::class,
                "choices" => $this->visualFormatRepository->findByCinemaAndActiveStatus($cinema, true),
                'choice_label' => 'name',
                "attr" => [
                    "readonly" => true
                ],
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add("remove", RemoveButtonType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningRoomSetup::class,
            "query_constraint" => null
        ]);
    }
}
