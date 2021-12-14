<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class CatalogExport
{
    /**
     * @var string
     */
    private $ftpDir;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * CatalogExport constructor.
     * @param string $ftpDir
     * @param LoggerInterface|null $logger
     * @param EntityManagerInterface $entityManager
     * @param \App\Service\CurrencyConversor $currencyConversor
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger = null, CatalogRepository $catalogRepository, ProductRepository $productRepository, string $ftpDir)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->catalogRepository = $catalogRepository;
        $this->productRepository = $productRepository;
        $this->ftpDir = $ftpDir;
    }

    /**
     * @return bool
     *
     * Process all files that are imported and export it to a CSV file
     */
    public function handleMultiple(): bool
    { 
        $catalogs = $this->catalogRepository->getImportedCatalogs();
        foreach ($catalogs as $catalog) {
            try {
                //Retrieve only products that are unsync
                $products = $catalog->getProducts(true);

                //If the directory where the CSV is going to be stored does not exist, creat it.
                if (!file_exists($this->ftpDir)) {
                    mkdir($this->ftpDir, 0777);
                }
        
                //Make a blank CSV file. The name would have a timestamp if it was in production, but for testing I only use catalog id.
                $handle = fopen($this->ftpDir.'/'.$catalog->getId().'.csv', 'w');
                
                //Header of the file
                $csv = "Product Id, Product Name, Price, Image 1, Image 2, Image 3, Image 4, Image 5, Image 6, Image 7, Image 8, Image 9 \n";
                
                $ids = [];
                foreach ($products as $x => $product) {
                    //Write the product data
                    $csv.= implode(",", array_values($product->toArray()))."\n";
                    
                    array_push($ids,$product->getStyleNumber());
                    
                    //For perfomance only flush 1000 records at once
                    if ($x % 1000 == 0) {
                        $qb = $this->entityManager->createQueryBuilder();
                        $qb->update('App\Entity\Product', 'p')
                        ->set('p.state', ':sync')
                        ->where('p.style_number IN (:ids)')
                        ->setParameter('ids', $ids)
                        ->setParameter('sync', 'sync');
                        $qb->getQuery()->execute();

                        $ids = [];
                    }
                }

                $qb = $this->entityManager->createQueryBuilder();
                $qb->update('App\Entity\Product', 'p')
                ->set('p.state', ':sync')
                ->where('p.style_number IN (:ids)')
                ->setParameter('ids', $ids)
                ->setParameter('sync', 'sync');
                $qb->getQuery()->execute();

                //Write the CSV
                fwrite($handle, $csv);
                
                //Close the CSV
                fclose($handle);

                $catalog->setState('sync');
                $this->entityManager->flush();

            } catch (Exception $e) {
                $this->logger->error('An Exception has ocurred: '. $e->getMessage(), ['catalog' => $catalog->getId(), 'state' => $catalog->getState()]);
            }
        }
        return true;
    }

    /**
     * @param Catalog $catalog
     * @return bool
     *
     * Process a file that is imported and export it to a CSV file
     */
    public function handleSingle(Catalog $catalog): bool
    { 
        try {
            //Retrieve only products that are unsync
            $products = $catalog->getProducts(true);

            //If the directory where the CSV is going to be stored does not exist, creat it.
            if (!file_exists($this->ftpDir)) {
                mkdir($this->ftpDir, 0777);
            }
    
            //Make a blank CSV file. The name would have a timestamp if it was in production, but for testing I only use catalog id.
            $handle = fopen($this->ftpDir.'/'.$catalog->getId().'.csv', 'w');
            
            //Header of the file
            $csv = "Product Id, Product Name, Price, Image 1, Image 2, Image 3, Image 4, Image 5, Image 6, Image 7, Image 8, Image 9 \n";
            
            $ids = [];
            foreach ($products as $x => $product) {
                //Write the product data
                $csv.= implode(",", array_values($product->toArray()))."\n";
                
                array_push($ids,$product->getStyleNumber());
                
                //For perfomance only flush 1000 records at once
                if ($x % 1000 == 0) {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->update('App\Entity\Product', 'p')
                    ->set('p.state', ':sync')
                    ->where('p.style_number IN (:ids)')
                    ->setParameter('ids', $ids)
                    ->setParameter('sync', 'sync');
                    $qb->getQuery()->execute();

                    $ids = [];
                }
            }

            $qb = $this->entityManager->createQueryBuilder();
            $qb->update('App\Entity\Product', 'p')
            ->set('p.state', ':sync')
            ->where('p.style_number IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('sync', 'sync');
            $qb->getQuery()->execute();

            //Write the CSV
            fwrite($handle, $csv);
            
            //Close the CSV
            fclose($handle);

            $catalog->setState('sync');
            $this->entityManager->flush();

        } catch (Exception $e) {
            $this->logger->error('An Exception has ocurred: '. $e->getMessage(), ['catalog' => $catalog->getId(), 'state' => $catalog->getState()]);
        }
        return true;
    }

    /**
     * @return string
     */
    public function getFtpDir()
    {
        return $this->ftpDir;
    }
}