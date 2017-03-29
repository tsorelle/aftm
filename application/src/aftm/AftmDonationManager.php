<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/29/2017
 * Time: 9:43 AM
 */

namespace Application\Aftm;
use PDO;

class AftmDonationManager
{
    /**
     * @var AftmDonationManager
     */
    private static $instance;
    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmDonationManager();
        }
        return self::$instance;
    }


    public function getDonationByInvoiceNumber($invoiceNumber) {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftmdonations WHERE donationnumber = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $invoiceNumber);
        $statement->execute();
        $donation = $statement->fetch(PDO::FETCH_OBJ);
        if ($donation === false) {
            return false;
        }

        return $donation;
    }
    public function updatePaymentInfo($invoiceNumber,$amount,$memo='')
    {
        $db = \Database::connection();
        $today = date('Y-m-d');
        $count = $db->executeUpdate('UPDATE aftmdonations SET datereceived=?, amount=?, paypalmemo=? WHERE donationnumber = ?',
            array($today,$amount,$memo,$invoiceNumber));

        $entry = $this->getDonationByInvoiceNumber($invoiceNumber);

        return $entry;
    }


    public function insertDonationEntry($donationData) {

        $insertValues = array(
            'firstname'    => $donationData->donor_first_name,
            'lastname'    => $donationData->donor_last_name,
            'donationnumber'    => $donationData->donation_invoice_number,
            'address1'    => $donationData->donor_address1,
            'address2'    => $donationData->donor_address2,
            'city'    => $donationData->donor_city,
            'state'    => $donationData->donor_state,
            'postalcode'    => $donationData->donor_zipcode,
            'email'    => $donationData->donor_email,
            'phone'    => $donationData->donor_phone);

        $db = \Database::connection();
        $db->insert('aftmdonations', $insertValues);
        $id = $db->lastInsertId();

        return $id;
    }

    public static function AddDonation($donationData) {
        self::getInstance()->insertDonationEntry($donationData);
    }
    public static function UpdatePayment($invoiceNumber,$amount,$memo='') {
        return self::getInstance()->updatePaymentInfo($invoiceNumber,$amount,$memo);
    }



}