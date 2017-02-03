<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/30/2017
 * Time: 8:02 AM
 */

namespace Application\Aftm;
use PDO;

/**
 * Class AftmInvoiceManager
 * @package Application\aftm
 *
 * Manages MySql data storage or invoices.
 * See /packages/aftm/db.xml for schema
 */
class AftmInvoiceManager
{
    /**
     * @var AftmInvoiceManager
     */
    private static $instance;
    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmInvoiceManager();
        }
        return self::$instance;
    }

    /**
     * Instance method
     * Add invoice and items to database
     *
     * @param array $invoiceData
     * @param array $items
     * @return mixed
     */
    public function postInvoice(Array $invoiceData, Array $items) {
        $db = \Database::connection();
        $db->insert('aftminvoices', $invoiceData);
        $id = $db->lastInsertId();
        foreach ($items as $item) {
            $item['invoiceid'] = $id;
            $db->insert('aftminvoiceitems', $item);
        }
        return $id;
    }

    public function updateInvoice($invoicenumber) {
        $db = \Database::connection();
        $today = date('Y-m-d');
        $count = $db->executeUpdate('UPDATE aftminvoices SET paid = 1, paiddate=? WHERE invoicenumber = ?', array($today,$invoicenumber));
        return $count;
    }

    /**
     * @param $invoicenumber
     * @return bool|\stdClass
     */
    public function GetInvoice($invoicenumber) {
        $result = new \stdClass();
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftminvoices WHERE invoicenumber = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $invoicenumber);
        $statement->execute();
        $result->invoice = $statement->fetch(PDO::FETCH_OBJ);
        if ($result->invoice === false) {
            return false;
        }

        if (isset($result->invoice->id)) {
            $sql = 'SELECT * FROM aftminvoiceitems WHERE invoiceid = ?';
            $statement = $db->prepare($sql);
            $statement->bindValue(1, $result->invoice->id);
            $statement->execute();
            $result->items = $statement->fetchAll(PDO::FETCH_OBJ);
        }
        else {
            $result->items = array();
        }
        
        return $result;
    }

    /**
     * Singleton method
     * Add invoice and items to database
     * return formatted invoice number
     *
     * @param $invoiceData
     * @param $invoiceItems
     * @return string
     */
    public static function Post($invoiceData,$invoiceItems) {
        $id = self::getInstance()->postInvoice($invoiceData,$invoiceItems);
        return sprintf('%08d', $id);
    }

    public static function Update($invoicenumber) {
        self::getInstance()->updateInvoice($invoicenumber);
    }

    public static function Get($invoicenumber) {
        return self::getInstance()->GetInvoice($invoicenumber);
    }
}