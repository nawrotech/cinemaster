<?php

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class MovieFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $movie = $options["data"] ?? null;
        $isEdit = $movie && $movie->getId();
        
        $builder
            ->add('title', TextType::class, [
            ])
            ->add('overview', TextareaType::class, [
                "required" => false,
                "attr" => [
                    "placeholder" => $options["defaults"]?->getOverview()
                ]
            ])
            ->add('durationInMinutes');

        $constraints = [
            new Image(
                [
                    "maxSize" => "2M",
                ]
            ),
        ];  

        if (!$isEdit || !$movie->getPosterPath()) {
            $constraints[] = new NotNull([
                "message" => "Please upload an image"
            ]);
        }

        $builder
            ->add("poster", FileType::class, [
                "label" => "Poster path (.png, .jpg, .webp)",
                "mapped" => false,
                "required" => false,
                "constraints" => $constraints
            ])
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
