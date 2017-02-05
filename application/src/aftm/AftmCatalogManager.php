<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/5/2017
 * Time: 6:55 AM
 */

namespace Application\Aftm;


class AftmCatalogManager
{
    private static $instance;

    private static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new AftmCatalogManager();
        }
        return self::$instance;
    }

    private function getPriceByProductType($productName, $typeName) {
        // todo: finish implementation with database

        // temporary pending database implementation
        if ($productName == 'membership') {
            switch ($typeName) {
                case "Student 1-year" : return     15.00;
                case "Individual 1-year" : return  20.00;
                case "Family 1-year" : return  25.00;
                case "Band or Dance Group 1-year" : return 25.00;
                case "Business 1-year" : return 50.00;
                case "Individual 5-year" : return 80.00;
                case "Family 5-year" : return  100.00;
                case "Lifetime membership" : return 300.00;
            }
        }
        return 0;
    }

    private function getTypesForProduct($productName)
    {
        // todo: finish implementation
        $result = array();
        if ($productName === 'membership') {

        }
        return $result;
    }
    public static function GetProductTypes($productName)
    {
        return self::getInstance()->getTypesForProduct($productName);
    }

    public static function GetProductTypeInfo($productName,$typeName)
    {
        return self::getInstance()->getTypesForProduct($productName);
    }

    public static function GetPrice($productName,$typeName) {
        return self::getInstance()->getPriceByProductType($productName,$typeName);
    }
}