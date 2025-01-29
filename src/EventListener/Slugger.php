<?php

namespace App\EventListener;

use App\Contract\SlugInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;


#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class Slugger 
{
    public function __construct(private SluggerInterface $slugger)
    {   
    }

    public function prePersist(PrePersistEventArgs $args): void 
    {
        $entity = $args->getObject();

        if (!$entity instanceof SlugInterface) {
            return;
        }

        $entity->setSlug($this->slugger->slug((string) $entity)->lower());
    }

    public function preUpdate(PreUpdateEventArgs $args): void 
    {
        $entity = $args->getObject();

        if (!$entity instanceof SlugInterface) {
            return;
        }

        $entity->setSlug($this->slugger->slug((string) $entity)->lower());
    }

 
}