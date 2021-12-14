<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Catalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Catalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Catalog[]    findAll()
 * @method Catalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    /**
     * Fetch all the catalogs that are submitted.
     * 
     * @return Catalog[] Returns an array of Catalog objects
     */
    public function getSubmittedCatalogs()
    {
        return $this->findBy(['state' => 'submitted']);
        ;
    }

    /**
     * Fetch all the catalogs that are imported.
     * 
     * @return Catalog[] Returns an array of Catalog objects
     */
    public function getImportedCatalogs()
    {
        return $this->findBy(['state' => 'imported']);
        ;
    }

    /**
     * Fetch all the catalogs that are synced.
     * 
     * @return Catalog[] Returns an array of Catalog objects
     */
    public function getSyncCatalogs()
    {
        return $this->findBy(['state' => 'sync']);
        ;
    }

}
