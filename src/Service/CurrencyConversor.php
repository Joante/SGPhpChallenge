<?php

namespace App\Service;

use App\Entity\Product;

/**
 * Class CurrencyConversor
 * @package App\Service
 *
 * Service used to convert currency
 */
class CurrencyConversor
{
    /**
     * Convert amount
     * @param Product $product
     * @param string $currencyToConvert
     * @return false|float
     * @throws \Exception
     */
    public function convertProductPrice(Product $product, $currencyToConvert)
    {
        //Call to an api to convert the price
        $jsonData = json_decode(file_get_contents("https://api.exchangerate.host/convert?from=".$product->getPriceCurrency()."&to=".$currencyToConvert."&amount=".$product->getPriceAmount()."places=2"),true);
        
        if (!isset($jsonData['result'])) {
            throw new \Exception("No currency rates founded");
        }

        //Convert the result
        $amount = intval($jsonData['result']);
        if($amount <= 1){
            return false;
        }

        return $jsonData['result'];
    }
}
