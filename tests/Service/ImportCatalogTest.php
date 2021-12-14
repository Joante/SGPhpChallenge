<?php

namespace App\Tests\Service;

use App\Entity\Catalog;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportCatalogTest extends KernelTestCase
{
    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->catalogImport = $kernel->getContainer()
            ->get('catalog_import');

        parent::setUp();
    }

    /**
     * Test single large import method
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testSingleLarge tests/Service/ImportCatalogTest.php
     * @endcode
     *
     */
    public function testSingleLarge()
    {
        $catalog = new Catalog();
        $catalog->setFilePath('tests/catalogSingleLarge.json');

        //Test the single import method with a large file.
        $answer = $this->catalogImport->handleSingle($catalog);

        //Check the results of the method
        $this->assertNotFalse($answer);
        $this->assertGreaterThan(0, count($catalog->getProducts()));
    }

    /**
     * Test single small import method
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testSingleSmall tests/Service/ImportCatalogTest.php
     * @endcode
     *
     */
    public function testSingleSmall()
    {
        $catalog = new Catalog();
        $catalog->setFilePath('tests/catalogSingleSmall.json');

        //Test the single import method with a small file.
        $answer = $this->catalogImport->handleSingle($catalog);

        //Check the results of the method
        $this->assertNotFalse($answer);
        $this->assertGreaterThan(0, count($catalog->getProducts()));
    }

    /**
     * Test multiple files import method
     *
     * To run the testcase:
     * @code
     * php ./bin/console doctrine:fixtures:load
     * ./vendor/bin/phpunit --filter testMultiple tests/Service/ImportCatalogTest.php
     * @endcode
     *
     */
    public function testMultiple()
    {
        //Test the multiple import method.
        $answer = $this->catalogImport->handleMultiple();

        //Check the results of the method
        $this->assertNotFalse($answer);
    }

    /**
     * Test bad JSON file
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testBadFile tests/Service/ImportCatalogTest.php
     * @endcode
     *
     */
    public function testBadFile()
    {
        $catalog = new Catalog();
        $catalog->setFilePath('tests/catalogBadFile.json');

        //Check the expect Exception
        $this->expectException(Exception::class);

        //Test the single import method.
        $this->catalogImport->handleSingle($catalog);

        
    }

    
}
