<?php

namespace App\Form;

use App\Entity\ScreeningFormat;
use App\Entity\VisualFormat;
use App\Enum\LanguagePresentation;
use App\Form\Type\RemoveButtonType;
use App\Repository\VisualFormatRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScreeningFormatType extends AbstractType
{
    public function __construct(private VisualFormatRepository $visualFormatRepository)
    {
        $this->visualFormatRepository = $visualFormatRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cinema = $options["cinema"];
     

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) use($cinema) {
                $screeningFormat = $event->getData();
                $form = $event->getForm();
        
                if ($screeningFormat?->getId()) {
                    $form->add("languagePresentation", TextType::class, [
                        "mapped" => false,
                        "data" => $screeningFormat->getLanguagePresentation()->value,
                        "attr" => [
                            "readonly" => true
                        ],
                    ]);
                    $form->add("visualFormat", TextType::class, [
                        "mapped" => false,
                        "data" => $screeningFormat->getVisualFormat()->getName(),
                        "attr" => [
                            "readonly" => true
                        ],

                    ]);
                }
            })
            ->add('languagePresentation', EnumType::class, [
                "class" => LanguagePresentation::class,
                'choice_label' => fn (LanguagePresentation $languagePresentation): string => $languagePresentation->value,
            ])
            ->add('visualFormat', EntityType::class, [
                'class' => VisualFormat::class,
                "choices" => $this->visualFormatRepository->findByCinemaAndActiveStatus($cinema, true),
                'choice_label' => 'name',
               
            ])
            ->add("remove", RemoveButtonType::class);
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningFormat::class,
            "cinema" => null
        ]);
    }
}
