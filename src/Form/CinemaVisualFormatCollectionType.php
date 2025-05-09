<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\VisualFormat;
use App\Repository\VisualFormatRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CinemaVisualFormatCollectionType extends AbstractType
{

    public function __construct(private VisualFormatRepository $visualFormatRepository)
    {   
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    
        /** @var Cinema $cinema */
        $cinema = $options['data'];

        if ($cinema->getVisualFormats()->isEmpty()) {
            $visualFormat = new VisualFormat();
            $visualFormat->setCinema($cinema);
            $cinema->addVisualFormat($visualFormat);
        }

        $builder
            ->add('visualFormats', CollectionType::class, [
                "entry_type" => VisualFormatType::class,
                "label" => false,
                "entry_options" => [
                    "label" => false,
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,  
                "constraints" => [
                    new Valid()
                ],
            ])   
            ->add("addScreeningRoomSetups", SubmitType::class, [
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
