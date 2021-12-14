<?php

namespace App\EventListener;

use App\Entity\Product;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class ProductListener
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Product
     */
    private $product;

    public function __construct(EntityManagerInterface $entityManager)//,string $catalogsDir)
    {
        $this->entityManager = $entityManager;
        //$this->catalogsDir = $catalogsDir;
    }


    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        // Check if the entity if of type Product
        if (!$eventArgs->getEntity() instanceof Product) {
            return;
        }

        //Check if the entity has some change
        $changes = $eventArgs->getEntityChangeSet();
        if(count($changes)>0){
            //Set the state of the entity to null so it can be sync again
            $this->product = $eventArgs->getEntity();
            $this->product->setState(null);
        }
    }
}