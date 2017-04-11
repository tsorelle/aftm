<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/5/2017
 * Time: 6:55 AM
 */

namespace Application\Aftm;
use PDO;

class AftmCatalogManager
{
    private static $instance;

    private static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new AftmCatalogManager();
        }
        return self::$instance;
    }

    private function getPriceByItemType($itemname, $itemtype) {
        $db = \Database::connection();
        $statement = $db->executeQuery('SELECT unitprice FROM aftmcatalog WHERE itemname=? AND itemtype=?',
            array($itemname,$itemtype));
        $result = $statement->fetch();
        if (empty($result)) {
            return false;
        }
        return $result['unitprice'];
    }

    private function getCatalogForItem($itemname) {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftmcatalog WHERE itemname = ? AND active=1 order by displayorder';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $itemname);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_OBJ);

        return $results;
    }

    private function getSelectListItems($itemname, $unassigned=false) {
        $result = array();
        if (!empty($unassigned)) {
            $result[''] = $unassigned;
        }
        $items = $this->getCatalogForItem($itemname);
        foreach ($items as $item) {
            $result[$item->itemtype] = $item->itemdescription;
        }
        return $result;
    }

    private function getSelectListObjects($itemname, $valueField = 'itemtype') {
        $db = \Database::connection();

        $sql = "SELECT itemdescription as Name, $valueField as Value  FROM aftmcatalog WHERE itemname = ? AND active=1 order by displayorder";
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $itemname);
        $statement->execute();
        $results = $statement->fetchall(PDO::FETCH_OBJ);
        if ($results === false) {
            return false;
        }
        return $results;
    }

    public static function GetPrice($itemname,$itemtype) {
        return self::getInstance()->getPriceByItemType($itemname,$itemtype);
    }

    public static function GetCatalog($itemname) {
        return self::getInstance()->getCatalogForItem($itemname);
    }

    public static function GetSelectList($itemname,$unassigned=false) {
        return self::getInstance()->getSelectListItems($itemname,$unassigned);
    }

    public static function GetObjectList($itemname) {
        return self::getInstance()->getSelectListObjects($itemname);
    }
}