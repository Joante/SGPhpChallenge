<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Entity\Product;
use App\Repository\CatalogRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class CatalogImport
{
     /**
     * @var string
     */
    private $catalogsDir;

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
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var \App\Service\CurrencyConversor
     */
    private $currencyConversor;

    /**
     * CatalogImport constructor.
     * @param string $catalogsDir
     * @param LoggerInterface|null $logger
     * @param EntityManagerInterface $entityManager
     * @param \App\Service\CurrencyConversor $currencyConversor
     */
    public function __construct(string $catalogsDir, LoggerInterface $logger = null, EntityManagerInterface $entityManager, CatalogRepository $catalogRepository, ProductRepository $productRepository, CurrencyConversor $currencyConversor)
    {
        $this->catalogsDir = $catalogsDir;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->catalogRepository = $catalogRepository;
        $this->productRepository = $productRepository;
        $this->currencyConversor = $currencyConversor;
    }

    /**
     * @param Catalog $catalog
     * @return bool|int
     *
     * Process file and store each row as a new Product
     */
    public function handleSingle(Catalog $catalog)
    {
        try {
            $path = $this->catalogsDir.'/'.$catalog->getFilePath();

            //Variable to know how many products have error and wasnt import.
            $errors = 0;
            
            //Check if the file exists and then get all the data
            if (file_exists($path)) {
                
                $jsonData = $this->loadResource($path);

                //Iterate all the data and add the product
                foreach ($jsonData as $x => $row) {
                    $error = false;
                    /* $product = new Product(); */
                    if (!empty($row['style_number'])) {
                        $product = $this->productRepository->find($row['style_number']);
                        if($product === null) {
                            $product = new Product();
                        }
                        $product->setStyleNumber($row['style_number']);
                    }else {
                        $error = true;
                    }

                    if (!empty($row['name']) && !$error) {
                        $product->setName($row['name']);
                    }else {
                        $error = true;
                    }

                    if (!empty($row['price']) && !$error) {
                        if (!empty($row['price']['currency'])) {
                             $product->setPriceCurrency($row['price']['currency']);
                        }else {
                            $error = true;
                        }

                        if (!empty($row['price']['amount'])) {
                            $product->setPriceAmount($row['price']['amount']);
                        }else {
                            $error = true;
                        }

                        //If product have a different currency than USD convert it
                        if (!empty($row['price']['amount']) && !empty($row['price']['currency'])) {
                            if ($product->getPriceCurrency() != 'USD') {
                                //If the API supports the given currency exchange it
                                $rateConvertion = $this->currencyConversor->convertProductPrice($product, 'USD');
                                if($rateConvertion){
                                    $product->setPriceAmount($rateConvertion);
                                    $product->setPriceCurrency('USD');
                                }else 
                                {
                                    $product->setPriceAmount($row['price']['amount']);
                                    $product->setPriceCurrency($row['price']['currency']);
                                }
                            }
                        }
                    }else {
                        $error = true;
                    }
                    if (!empty($row['images']) && !$error) {
                        $product->setImages($row['images']);                       
                    }else {
                        $error = true;
                    }
                    if(!$error) {              
                        //Associate the products with the given catalog        
                        $catalog->addProduct($product);

                        $this->entityManager->persist($product);
                        //For perfomance only flush 1000 records at once
                        if ($x % 1000 == 0) {
                            $this->entityManager->flush();
                        }
                    }else {
                        $errors++;
                    }
                }
                $catalog->setState('imported');
                
                $this->entityManager->flush();
            }

            return $errors;
        } catch (Exception $e) {
            $this->logger->error('An Exception has ocurred: '. $e->getMessage(), ['catalog' => $catalog->getId(), 'state' => $catalog->getState()]);
        }

        return false;
    }

    /**
     * @return bool|array
     *
     * Process all files that are submitted and store each file row as a new Product
     */
    public function handleMultiple()
    { 
        $catalogs = $this->catalogRepository->getSubmittedCatalogs();
        
        $errors = [];
        foreach ($catalogs as $catalog) {
            $result = $this->handleSingle($catalog);
            if(!$result) {
                return false;
            }else if($result > 0){
                $errors[$catalog->getId()] = $result;
            }
        }
        return $errors;
    }

    /**
     * This function reads the file, decode it and look for errors in the decode process.
     * 
     */
    protected function loadResource(string $resource)
    {
        $products = [];
        if ($data = file_get_contents($resource)) {
            $products = json_decode($data, true);

            if (0 < $errorCode = json_last_error()) {
                throw new Exception('Error parsing JSON: '.$this->getJSONErrorMessage($errorCode));
            }
        }

        return $products;
    }

    /**
     * Translates JSON_ERROR_* constant into meaningful message.
     */
    private function getJSONErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case \JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case \JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case \JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case \JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case \JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
}