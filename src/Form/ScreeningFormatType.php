<?php

namespace App\Form;

use App\Entity\ScreeningFormat;
use App\Entity\VisualFormat;
use App\Enum\LanguagePresentation;
use App\Form\Type\RemoveButtonType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class ScreeningFormatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cinema = $options["query_constraint"];

        $builder
            ->add('languagePresentation', ChoiceType::class, [
                "choices" => LanguagePresentation::getValuesArray(),
                "choice_label" => function($choice): TranslatableMessage|string {
                    return $choice;
                },

            ])
            ->add('visualFormat', EntityType::class, [
                'class' => VisualFormat::class,
                "query_builder" => function(EntityRepository $er) use($cinema): QueryBuilder  {
                    return $er->createQueryBuilder("vf")
                            ->where("vf.cinema = :cinema")
                            ->setParameter("cinema", $cinema);
                },

                'choice_label' => 'name',
            ])
            ->add("remove", RemoveButtonType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScreeningFormat::class,
            "query_constraint" => null
        ]);
    }
}
