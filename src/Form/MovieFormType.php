<?php

namespace App\Form;

use App\Entity\Format;
use App\Entity\Movie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('durationInMinutes')
            ->add('movieFormats', EntityType::class, [
                "mapped" => false,
                "label" => "Choose movie's formats",
                "class" => Format::class,
                'choice_label' => function (Format $format): string {
                    return "{$format->getAudioVersion()} {$format->getVisualVersion()}";;
                },
                "multiple" => true,
                "expanded" => true
            ])
            ->add("save", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
