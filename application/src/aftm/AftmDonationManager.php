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


    public function getDonationById($donationId) {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftmdonations WHERE id = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $donationId);
        $statement->execute();
        $donation = $statement->fetch(PDO::FETCH_OBJ);
        if ($donation === false) {
            return false;
        }

        return $donation;
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


    public function updateDonationRecord($donationData) {

        $updateValues = array(
            'firstname'    		=> $donationData->firstname,
            'lastname'    		=> $donationData->lastname,
            'donationnumber'    => $donationData->donationnumber,
            'address1'    		=> $donationData->address1,
            'address2'    		=> $donationData->address2,
            'city'    			=> $donationData->city,
            'state'    			=> $donationData->state,
            'postalcode'    	=> $donationData->postalcode,
            'email'    			=> $donationData->email,
            'phone'    			=> $donationData->phone,
            'amount'      		=> $donationData->amount,
            'datereceived'      => $donationData->datereceived,
            'notes'             => $donationData->notes,
            'paypalmemo'        => $donationData->paypalmemo  );




        $db = \Database::connection();
        $count = $db->update('aftmdonations', $updateValues,array('id'=> $donationData->id));

        return $count;
    }


    public function insertDonation($donationData) {

        $insertValues = array(
            'firstname'    		=> $donationData->firstname,
            'lastname'    		=> $donationData->lastname,
            'donationnumber'    => $donationData->donationnumber,
            'address1'    		=> $donationData->address1,
            'address2'    		=> $donationData->address2,
            'city'    			=> $donationData->city,
            'state'    			=> $donationData->state,
            'postalcode'    	=> $donationData->postalcode,
            'email'    			=> $donationData->email,
            'phone'    			=> $donationData->phone,
            'amount'      		=> $donationData->amount,
            'datereceived'      => $donationData->datereceived,
            'notes'             => $donationData->notes,
            'paypalmemo'        => $donationData->paypalmemo  );


        $db = \Database::connection();
        $db->insert('aftmdonations', $insertValues);
        $id = $db->lastInsertId();

        return $id;
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

    public function getDonationYears() {
        $db = \Database::connection();
        $sql = 'SELECT DISTINCT DATE_FORMAT(datereceived,\'%Y\') AS donationyear FROM aftmdonations GROUP BY datereceived DESC';
        $statement = $db->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        $years = array();
        foreach ($result as $record) {
            $years[] = $record['donationyear'];
        }
        return $years;
    }

    public function getDonations($year=null) {
        $db = \Database::connection();
        $sql = 'SELECT id,donationnumber,DATE_FORMAT(datereceived,\'%Y-%m-%d\') AS datereceived,amount,firstname,lastname,email,phone FROM aftmdonations';
        if ($year) {
            $sql .= " WHERE YEAR(datereceived) = ? ORDER BY datereceived DESC";
            $statement = $db->prepare($sql);
            $statement->bindValue(1, $year);
        }
        else{
            $sql .= " ORDER BY datereceived DESC";
            $statement = $db->prepare($sql);
        }
        $statement->execute();
        $donations = $statement->fetchAll(PDO::FETCH_OBJ);
        if ($donations === false) {
            return false;
        }
        return $donations;
    }

    public static function AddDonation($donationData) {
        self::getInstance()->insertDonationEntry($donationData);
    }
    public static function UpdatePayment($invoiceNumber,$amount,$memo='') {
        return self::getInstance()->updatePaymentInfo($invoiceNumber,$amount,$memo);
    }

    public static function GetDonationList($year=null) {
        return self::getInstance()->getDonations($year);
    }

    public static function GetDonationYearList() {
        return self::getInstance()->getDonationYears();
    }

    public static function GetDonation($donationId) {
        return self::getInstance()->getDonationById($donationId);
    }

    public static function UpdateDonation($donationData) {
        return self::getInstance()->updateDonationRecord($donationData);
    }

    public static function NewDonation($donationData) {
        return self::getInstance()->insertDonation($donationData);
    }


}