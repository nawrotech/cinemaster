<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Repository\ScreeningRoomSetupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CinemaScreeningRoomSetupCollectionType extends AbstractType
{

    public function __construct(private ScreeningRoomSetupRepository $screeningRoomSetupRepository)
    {   
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Cinema $cinema */
        $cinema = $options['data'];
        $screeningRoomSetups = $this->screeningRoomSetupRepository->findByCinemaAndActiveStatus($cinema, true);

        $builder
           ->add("screeningRoomSetups", CollectionType::class, [
                "data" => $screeningRoomSetups,
                "label" => "Enter types can be played in your screening rooms",
                "entry_type" => ScreeningRoomSetupType::class,
                "entry_options" => [
                    "label" => false,
                    "query_constraint" => $cinema
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
           ])
           ->add("addScreeningFormats", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-primary"
                ]
            ])
            ->add("submit", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-secondary"
                ]
            ])
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
        ]);
    }
}
