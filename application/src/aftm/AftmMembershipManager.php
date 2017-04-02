<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/1/2017
 * Time: 7:17 AM
 */

namespace Application\Aftm;


class AftmMembershipManager
{
    private static $instance;
    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmMembershipManager();
        }
        return self::$instance;
    }

    public function interestsToString($memberData) {
        $result = '';

        if (isset($memberData->volunteer) ) {
            if ($memberData->volunteer->concerts	) { $result .= 'concerts'; }
            if ($memberData->volunteer->newsletter) { $result .= (empty($result)? '' : ', ').'newsletter'; }
            if ($memberData->volunteer->publicity ) { $result .= (empty($result)? '' : ', ').'publicity'; }
            if ($memberData->volunteer->festivals ) { $result .= (empty($result)? '' : ', ').'festivals'; }
            if ($memberData->volunteer->membership) { $result .= (empty($result)? '' : ', ').'membership'; }
            if ($memberData->volunteer->mailings  ) { $result .= (empty($result)? '' : ', ').'mailings'; }
            if ($memberData->volunteer->webpage   ) { $result .= (empty($result)? '' : ', ').'webpage'; }
        }

        return $result;
    }



    public function getMembershipByInvoiceNumber($invoiceNumber)
    {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftmmemberships WHERE invoicenumber = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $invoiceNumber);
        $statement->execute();
        $membership = $statement->fetch(PDO::FETCH_OBJ);
        if ($membership === false) {
            return false;
        }

        return $membership;
    }



    public function updatePaymentInfo($invoiceNumber,$cost,$memo)
    {
        $today = date('Y-m-d');

            $db = \Database::connection();
            $today = date('Y-m-d');
            $count = $db->executeUpdate('UPDATE aftmmemberships SET paymentreceiveddate=?, amount=?, paypalmemo=? WHERE invoicenumber = ?',
                array($today,$cost,$memo,$invoiceNumber));

            $entry = $this->getMembershipByInvoiceNumber($invoiceNumber);

            return $entry;

    }

    private function getMembershipTerm($membershipType) {
        $membershipType = strtolower(str_replace('-',' ',$membershipType));
        if (strstr($membershipType,'lifetime')) {
            return 100;
        }
        if (strstr($membershipType,'5 year')) {
            return 5;
        }
        return 1;
    }


    public function insertMembershipEntry($memberData) {
        $membershipType = isset($memberData->membership_type) ? $memberData->membership_type : '';
        $interests = $this->interestsToString($memberData);
        $expirationYears = $this->getMembershipTerm($membershipType);
        $today = date('Y-m-d');
        $dateParts = explode('-',$today);
        $dateParts[0] = ($dateParts[0] + $expirationYears);
        $expirationDate = implode('-',$dateParts);
        $insertValues = array(
            'firstname'  	=> $memberData->member_first_name,
            'lastname'  	=> $memberData->member_last_name,
            'invoicenumber' => $memberData->invoicenumber,
            'address1'  	=> $memberData->member_address1,
            'address2'  	=> $memberData->member_address2,
            'state'  		=> $memberData->member_city,
            'city'  		=> $memberData->member_state,
            'postalcode'  	=> $memberData->member_zipcode,
            'email'  		=> $memberData->member_email,
            'membershiptype' => $memberData->membership_type,
            'startdate'		 => $today,
            'reneweddate'    => $today,
            'expirationdate' => $expirationDate,
            'groupname'  	=> $memberData->member_band_name,
            'groupwebsite'  => $memberData->member_band_website,
            'volunteerinterests'  => $interests,
            'paymentmethod' => $memberData->payment_method,
            'neworrenewal'  => $memberData->new_or_renewal,
            'ideas'  		=> $memberData->member_ideas );

        $db = \Database::connection();
        $db->insert('aftmmemberships', $insertValues);
        $id = $db->lastInsertId();

        return $id;
    }

    //  MANAGEMENT FORM SUPPORT

    public function getMembershipById($membershipId) {
        $db = \Database::connection();

        $sql = 'SELECT * FROM aftmmemberships WHERE id = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $membershipId);
        $statement->execute();
        $membership = $statement->fetch(PDO::FETCH_OBJ);
        if ($membership === false) {
            return false;
        }

        return $membership;
    }


    private function getUpdateValues($membershipData) {
        $expirationYears = $this->getMembershipTerm($membershipData->membershiptype);
        $today = date('Y-m-d');
        if (empty($membershipData->reneweddate)) {
            $membershipData->reneweddate = $today;
        }
        if (empty($membershipData->startdate)) {
            $membershipData->startdate = $membershipData->reneweddate;
        }
        $dateParts = explode('-',$membershipData->reneweddate);
        $dateParts[0] = ($dateParts[0] + $expirationYears);
        $membershipData->expirationdate = implode('-',$dateParts);

        $updateValues = array(
            'firstname'  	=>        $membershipData->firstname	,
            'lastname'  	=>        $membershipData->lastname	,
            'invoicenumber' =>        $membershipData->invoicenumber,
            'address1'  	=>        $membershipData->address1	,
            'address2'  	=>        $membershipData->address2	,
            'state'  		=>        $membershipData->state		,
            'city'  		=>        $membershipData->city		,
            'postalcode'  	=>        $membershipData->postalcode	,
            'email'  		=>        $membershipData->email		,
            'membershiptype' =>       $membershipData->membershiptype,
            'groupname'  	=>        $membershipData->groupname	,
            'groupwebsite'  =>        $membershipData->groupwebsite,
            'volunteerinterests'  =>  $membershipData->volunteerinterests,
            'ideas'  		=>        $membershipData->ideas		,
            'notes'  		=>        $membershipData->notes		,
            'startdate'     =>        $membershipData->startdate,
            'reneweddate'    =>       $membershipData->reneweddate,
            'expirationdate' =>       $membershipData->expirationdate,
            'paymentreceiveddate' =>  $membershipData->paymentreceiveddate
        );
    }
    public function updateMembershipRecord($membershipData) {

        $updateValues = $this->getUpdateValues($membershipData);
        $db = \Database::connection();
        $count = $db->update('aftmmemberships', $updateValues,array('id'=> $membershipData->id));

        return $count;
    }


    public function insertMembership($membershipData) {
        $insertValues = $this->getUpdateValues($membershipData);
        $db = \Database::connection();
        $db->insert('aftmmemberships', $insertValues);
        $id = $db->lastInsertId();

        return $id;
    }


    public function getMemberships($year=null) {
        $db = \Database::connection();
        $sql = 'SELECT id,invoicenumber,DATE_FORMAT(reneweddate,\'%Y-%m-%d\') AS reneweddate, membershiptype,firstname,lastname,email  FROM aftmmemberships';
        if ($year) {
            $sql .= " WHERE YEAR(reneweddate) = ? ORDER BY datereceived DESC";
            $statement = $db->prepare($sql);
            $statement->bindValue(1, $year);
        }
        else{
            $sql .= " ORDER BY datereceived DESC";
            $statement = $db->prepare($sql);
        }
        $statement->execute();
        $memberships = $statement->fetchAll(PDO::FETCH_OBJ);
        if ($memberships === false) {
            return false;
        }
        return $memberships;
    }

    public function getMembershipYears() {
        $db = \Database::connection();
        $sql = 'SELECT DISTINCT DATE_FORMAT(reneweddate,\'%Y\') AS membershipyear FROM aftmmemberships GROUP BY reneweddate DESC';
        $statement = $db->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        $years = array();
        foreach ($result as $record) {
            $years[] = $record['membershipyear'];
        }
        return $years;
    }

    public static function GetMemberInterests($memberData) {
        return self::getInstance()->interestsToString($memberData);
    }
    public static function AddMembership($memberData) {
        self::getInstance()->insertMembershipEntry($memberData);
    }
    public static function UpdatePayment($invoiceNumber,$cost,$memo) {
        return self::getInstance()->updatePaymentInfo($invoiceNumber,$cost,$memo);
    }
    public static function GetMembershipList($year=null) {
        return self::getInstance()->getMemberships($year);
    }

    public static function GetMembershipYearList() {
        return self::getInstance()->getMembershipYears();
    }

    public static function GetMembership($membershipId) {
        return self::getInstance()->getMembershipById($membershipId);
    }

    public static function UpdateMembership($membershipData) {
        return self::getInstance()->updateMembershipRecord($membershipData);
    }

    public static function NewMembership($membershipData) {
        return self::getInstance()->insertMembership($membershipData);
    }

}