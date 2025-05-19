<?php

namespace App\Form;

use App\Entity\Cinema;
use App\Entity\PriceTier;
use App\Repository\VisualFormatRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Valid;

class CinemaPriceTiersCollectionType extends AbstractType
{

    public function __construct(private VisualFormatRepository $visualFormatRepository)
    {   
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    
        /** @var Cinema $cinema */
        $cinema = $options['data'];

        if ($cinema->getPriceTiers()->isEmpty()) {
            $priceTier = new PriceTier();
            $priceTier->setCinema($cinema);
            $cinema->addPriceTier($priceTier);
        }

        $builder
            ->add('priceTiers', CollectionType::class, [
                "entry_type" => PriceTierType::class,
                "label" => false,
                "entry_options" => [
                    "label" => false,
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "by_reference" => false,
                "prototype" => true,  
                "constraints" => [
                    new Valid(),
                    new Count(max: 10)
                ],
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
