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

        $invoicenumber = sprintf('%08d', $id);
        $count = $db->executeUpdate('UPDATE aftminvoices SET  invoicenumber=? WHERE id = ?', array($invoicenumber,$id));

        return $invoicenumber;
    }

    public function updateInvoice($invoicenumber,$transactionId='') {
        $db = \Database::connection();
        $today = date('Y-m-d');
        $count = $db->executeUpdate('UPDATE aftminvoices SET paid = 1, paiddate=?, paypaltxnid = ? WHERE invoicenumber = ?',
            array($today,$transactionId,$invoicenumber));
        return $count;
    }

    private function dropInvoice($invoicenumber)
    {
        $db = \Database::connection();
        $sql = 'select id from aftminvoices where invoicenumber = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $invoicenumber);
        $statement->execute();
        $result = $statement->fetch();
        if ($result === false) {
            return false;
        }
        $id = $result['id'];
        $db->delete('aftminvoiceitems', array('invoiceid' => $id));
        return $db->delete('aftminvoices', array('id' => $id));
    }

    /**
     * @param $invoicenumber
     * @return bool|\stdClass
     */
    public function GetInvoice($invoicenumber) {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftminvoices WHERE invoicenumber = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $invoicenumber);
        $statement->execute();
        $invoice = $statement->fetch(PDO::FETCH_OBJ);
        if ($invoice === false) {
            return false;
        }

        if (isset($invoice->id)) {
            $sql = 'SELECT * FROM aftminvoiceitems WHERE invoiceid = ?';
            $statement = $db->prepare($sql);
            $statement->bindValue(1, $invoice->id);
            $statement->execute();
            $invoice->items = $statement->fetchAll(PDO::FETCH_OBJ);
        }
        else {
            $invoice->items = array();
        }
        
        return $invoice;
    }

    public function checkPaypalTransactionId($transactionId) {
        $db = \Database::connection();
        $statement = $db->executeQuery('SELECT 1 FROM aftminvoices WHERE paypaltxnid = ?', array($transactionId));
        $result = $statement->fetch();
        return  ($result !== false);
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
        return self::getInstance()->postInvoice($invoiceData,$invoiceItems);
    }

    public static function Update($invoicenumber,$transactionId='') {
        return self::getInstance()->updateInvoice($invoicenumber,$transactionId);
    }

    public static function Get($invoicenumber) {
        return self::getInstance()->GetInvoice($invoicenumber);
    }

    public static function CheckPaypalTransaction($transactionId) {
        return self::getInstance()->checkPaypalTransactionId($transactionId);
    }

    public static function RemoveInvoice($invoicenumber) {
        return self::getInstance()->dropInvoice($invoicenumber);
    }

}