<?php

namespace App\Tests\Service;

use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExportCatalogTest extends KernelTestCase
{
    /**
     * @var CatalogExport
     */
    private $catalogExport;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->catalogExport = $kernel->getContainer()
            ->get('catalog_export');

        $this->entityManager = $kernel->getContainer()
        ->get('doctrine')
        ->getManager();

        parent::setUp();
    }

    /**
     * Test Export method
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testExport tests/Service/ExportCatalogTest.php
     * @endcode
     *
     */
    public function testExport()
    {        
        //Test Export service and store the answer
        $answer = $this->catalogExport->handleMultiple();

        //Check if the answer is true
        $this->assertTrue($answer);

        //Check if the sync catalogs files exists in the ftp directory and erase them.
        $catalogRepository = $this->entityManager->getRepository('App:Catalog');
        $catalogs = $catalogRepository->getSyncCatalogs();

        foreach($catalogs as $catalog){
            $this->assertFileExists($this->catalogExport->getFtpDir().'/'.$catalog->getId().'.csv');
        
            if (file_exists($this->catalogExport->getFtpDir().'/'.$catalog->getId().'.csv')) {
                unlink($this->catalogExport->getFtpDir().'/'.$catalog->getId().'.csv');
            }
        }        
    }
}
