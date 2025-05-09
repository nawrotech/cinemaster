<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\ScreeningRoomSetup;
use App\Repository\ScreeningRoomSetupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CinemaScreeningRoomSetupCollectionType extends AbstractType
{

    public function __construct(private ScreeningRoomSetupRepository $screeningRoomSetupRepository)
    {   
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Cinema $cinema */
        $cinema = $options['data'];

        if ($cinema->getScreeningRoomSetups()->isEmpty()) {
            $screeningRoomSetup = new ScreeningRoomSetup();
            $screeningRoomSetup->setCinema($cinema);
            $cinema->addScreeningRoomSetup($screeningRoomSetup);
        }

        $builder
           ->add("screeningRoomSetups", CollectionType::class, [
                "label" => "Enter types that can be played in your screening rooms",
                "entry_type" => ScreeningRoomSetupType::class,
                "entry_options" => [
                    "label" => false,
                    "query_constraint" => $cinema,
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
                'constraints' => [
                    new Valid()
                ], 
                "error_bubbling" => false, 
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
