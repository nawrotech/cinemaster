<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\ScreeningFormat;
use App\Repository\ScreeningFormatRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Valid;

class CinemaScreeningFormatCollectionType extends AbstractType
{
    public function __construct(private ScreeningFormatRepository $screeningFormatRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Cinema $cinema */
        $cinema = $options['data'];

        if ($cinema->getScreeningFormats()->isEmpty()) {
            $screeningFormat = new ScreeningFormat();
            $screeningFormat->setCinema($cinema);
            $cinema->addScreeningFormat($screeningFormat);
        }

        $builder
           ->add("screeningFormats", CollectionType::class, [
                "label" => false,
                "entry_type" => ScreeningFormatType::class,
                "entry_options" => [
                    "label" => false,
                    "cinema" => $cinema
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,
                'constraints' => [
                    new Valid(),
                    new Count(max: 2)
                ]
           ])
           ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cinema::class,
        ]);
    }
}
