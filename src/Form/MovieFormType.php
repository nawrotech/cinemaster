<?php

namespace App\Form;

use App\Entity\Movie;
use App\Service\UploaderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class MovieFormType extends AbstractType
{

    public function __construct(
        private UploaderHelper $uploaderHelper,
        )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('title', TextType::class, [
            ])
            ->add('overview', TextareaType::class, [
                "required" => false,
                "attr" => [
                    "placeholder" => $options["defaults"]?->getOverview()
                ]
            ])
            ->add('durationInMinutes', IntegerType::class)
            ->add("poster", FileType::class, [
                "label" => "Poster path (.png, .jpg, .webp)",
                "mapped" => false,
                "required" => false,
                "constraints" => [
                    new Image(
                        [
                            "maxSize" => "2M",
                        ]
                    ),
                ]
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event): void {
                $movie = $event->getData();
                $posterPath = $movie->getPosterPath();
                if (!$posterPath) {
                    return;
                }
                $form = $event->getForm();
                $form->add("deletePoster", SubmitType::class, [
                    "attr" => [
                        "name" => "delete-poster",
                        "class" => "btn btn-danger",
                    ]
                ]);
                
                }
            )
            ->add("save", SubmitType::class)
        ;

   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
            "defaults" => null,
            
        ]);
    }
}
