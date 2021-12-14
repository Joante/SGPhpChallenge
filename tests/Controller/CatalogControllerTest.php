<?php

namespace App\Tests\Controller;

use App\Entity\Catalog;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CatalogControllerTest
 * @package App\Tests\Controller
 *
 * To run the testcase:
 * @code
 * ./bin/phpunit tests/Controller/CatalogControllerTest.php
 * @endcode
 */
class CatalogControllerTest extends WebTestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        parent::setUp();
    }

    /**
     * Test success when attaching a valid file (< max limit 17000k)
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testNewSuccess tests/Controller/CatalogControllerTest.php
     * @endcode
     *
     */
    public function testNewSuccess()
    {
        $path = 'tests/catalogSingleSmall.json';

        $file = new UploadedFile(
            $this->client->getContainer()->getParameter('catalogs_dir').'/'.$path,
            $path,
            'test/plain'
        );

        //Navigate to catalog new route
        $this->client->request('GET', '/');

        $this->client->followRedirect();
        
        $this->client->clickLink('Add Catalog');

        //Fill the form with the file and submit it
        $this->client->submitForm('Create', [
            'Catalog[file][file]' => $file,
        ]);
        
        //Check the response of the submit
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $lastInsertedCatalog = $this->entityManager->getRepository('App:Catalog')->findOneBy(
            array(),
            array('id' => 'DESC')
        );

        $this->assertInstanceOf(Catalog::class, $lastInsertedCatalog);
        $this->assertFileExists($this->client->getContainer()->getParameter('catalogs_dir').'/'.$lastInsertedCatalog->getFilePath());
    }

    /**
     * Test success when attaching a valid long file (< max limit 17000k)
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testNewSuccessLongFile tests/Controller/CatalogControllerTest.php
     * @endcode
     */
    public function testNewSuccessLongFile()
    {
        $path = 'tests/catalogSingleLarge.json';

        $file = new UploadedFile(
            $this->client->getContainer()->getParameter('catalogs_dir').'/'.$path,
            $path,
            'test/plain'
        );

        //Navigate to catalog new route
        $this->client->request('GET', '/');
        
        $this->client->followRedirect();
        
        $this->client->clickLink('Add Catalog');

        //Fill the form with the file and submit it
        $this->client->submitForm('Create', [
            'Catalog[file][file]' => $file,
        ]);
        

        //Check the response of the submit
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $lastInsertedCatalog = $this->entityManager->getRepository('App:Catalog')->findOneBy(
            array(),
            array('id' => 'DESC')
        );

        $this->assertInstanceOf(Catalog::class, $lastInsertedCatalog);
        $this->assertFileExists($this->client->getContainer()->getParameter('catalogs_dir').'/'.$lastInsertedCatalog->getFilePath());
    }

    /**
     * Test success when attaching a valid long file (= max limit 17000k)
     *
     * To run the testcase:
     * @code
     * ./vendor/bin/phpunit --filter testNewSuccessExtremeLongFile tests/Controller/CatalogControllerTest.php
     * @endcode
     */
    public function testNewSuccessExtremeLongFile()
    {
        $path = 'tests/catalogSingleLarge.json';

        $file = new UploadedFile(
            $this->client->getContainer()->getParameter('catalogs_dir').'/'.$path,
            $path,
            'test/plain'
        );

        //Navigate to catalog new route
        $this->client->request('GET', '/');
        
        $this->client->followRedirect();
        
        $this->client->clickLink('Add Catalog');

        //Fill the form with the file and submit it
        $this->client->submitForm('Create', [
            'Catalog[file][file]' => $file,
        ]);
        

        //Check the response of the submit
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $lastInsertedCatalog = $this->entityManager->getRepository('App:Catalog')->findOneBy(
            array(),
            array('id' => 'DESC')
        );

        $this->assertInstanceOf(Catalog::class, $lastInsertedCatalog);
        $this->assertFileExists($this->client->getContainer()->getParameter('catalogs_dir').'/'.$lastInsertedCatalog->getFilePath());
    }
}
