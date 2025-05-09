<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveButtonType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMapped(false);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "attr" => [
                "data-form-collection-target" => "removeButton",
                "data-action" => "click->form-collection#removeElement",
                "class" => "btn btn-danger"
            ],
            
        ]);
    }
  public function getParent(): string
    {
        return ButtonType::class;
    }
  
}