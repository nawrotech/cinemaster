<?php

namespace App\Form;

use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieScreeningFormatType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cinema = $options["cinema"];

        $builder
            ->add('movie', EntityType::class, [
                'class' => Movie::class,
                'choice_label' => "title",
            ])
            ->add('screeningFormats', EntityType::class, [
                "mapped" => false,
                "class" => ScreeningFormat::class,
                "label" => "Choose formats available in your cinema",
                "query_builder" => function(EntityRepository $er) use($cinema): QueryBuilder   {
                    return $er->createQueryBuilder("sf")
                            ->where("sf.cinema = :cinema")
                            ->setParameter("cinema", $cinema);
                },
                'choice_label' => function (ScreeningFormat $screeningFormat): string {
                    return "{$screeningFormat->getLanguagePresentation()} {$screeningFormat->getVisualFormat()->getName()}";;
                },                "multiple" => true,
                "expanded" => true
            ])
            ->add("remove", ButtonType::class, [
                "attr" => [
                    "data-form-collection-target" => "removeButton",
                    "data-action" => "click->form-collection#removeElement",
                    "class" => "btn btn-danger"
                ],

            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (PostSubmitEvent $event) use($cinema): void {
                $screeningFormats = $event->getForm()->get("screeningFormats")->getData();
                $movie = $event->getForm()->get("movie")->getData();

                foreach ($screeningFormats as $screeningFormat) {
                    $movieScreeningFormat = new MovieScreeningFormat();
                    $movieScreeningFormat->setCinema($cinema);
                    $movieScreeningFormat->setMovie($movie);
                    $movieScreeningFormat->setScreeningFormat($screeningFormat);

                    $this->em->persist($movieScreeningFormat);
                }

                $this->em->flush();
                
            })
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MovieScreeningFormat::class,
            "cinema" => null
        ]);
    }
}
