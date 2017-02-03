<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/30/2017
 * Time: 4:41 PM
 */

namespace Application\Aftm;
use Core;
use Concrete\Core\Entity\Express\ObjectManager;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Express\EntryList;
use DateTime;
use DateInterval;
// iam changed

class AftmMemberEntityManager
{
    const aftmMembershipHandle = 'aftm_membership';

    private static $instance;
    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmMemberEntityManager();
        }
        return self::$instance;
    }

    public function getEntity() {
        $memberEntity = Express::getObjectByHandle(self::aftmMembershipHandle);
        if (empty($memberEntity)) {
            throw new \Exception('Entity "AFTM Membership" does not exist');
           // return $this->createMemberEntity();
        }
        return $memberEntity;
    }

    /*

     EXPERIMENTAL, NOT READY
    public function createMemberEntity() {
        $member = Express::buildObject(self::aftmMembershipHandle, self::aftmMembershipHandle.'s', 'AFTM Memberships');
        $member->addAttribute('text','First name','member_first_name');
        $member->addAttribute('text','Last name','member_last_name');
        $member->addAttribute('text','Address1','member_address1');
        $member->addAttribute('text','Address2','member_address2');
        $member->addAttribute('text','City','member_city');
        $member->addAttribute('text','State or province','member_state');
        $member->addAttribute('text','Postal code or country','member_postal_code');
        $member->addAttribute('text','Email','member_email');
        $member->addAttribute('text','Membership type','membership_type');
        $member->addAttribute('text','Band name','member_band_name');
        $member->addAttribute('text','Band website','member_band_website');
        $member->addAttribute('text','Volunteer interest','member_volunteer_interest');
        $member->addAttribute('date_time','Start date','member_start_date');
        $member->addAttribute('date_time','Renewed date','member_renewed_date');
        $member->addAttribute('date_time','Expiration date','member_expiration_date');
        $member->addAttribute('text','Payment method','member_payment_method');
        $member->addAttribute('date_time','Payment received date','member_payment_received_date');
        $member->addAttribute('text','Invoice number','member_invoice_number');
        $member->addAttribute('text','New or renewal','new_or_renewal');
        $member->addAttribute('textarea', 'ideas', 'member_ideas');
        $member->addAttribute('textarea', 'notes', 'member_notes');

        $member->save();
        $memberEntity = Express::getObjectByHandle(self::aftmMembershipHandle);

        $form = $member->buildForm('Form');
        $form->addFieldset('Membership')
            ->addAttributeKeyControl('member_first_name')
            ->addAttributeKeyControl('member_last_name')
            ->addAttributeKeyControl('member_expiration_date');
        $form = $form->save();

        return $memberEntity;
    }
*/
    public function updatePaymentInfo($invoiceNumber) {
        $entity = $this->getEntity();
        $list = new EntryList($entity);
        $list->filterByMemberInvoiceNumber($invoiceNumber);
        $results = $list->getResults();
        if (!empty($results)) {
            $entry = $results[0];
            $today = date('Y-m-d');
            $entry->setMemberPaymentReceived_date($today);
            $entry->save();
        }
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
        // $this->getEntityOrCreate(); // ensure 'member' entity exists
        // $memberEntity = Express::getObjectByHandle('aftm_membership');
        $membershipType = isset($memberData['membership_type']) ? $memberData['membership_type'] : '';
        $expirationYears = $this->getMembershipTerm($membershipType);
        $today = date('Y-m-d');
        $dateParts = explode('-',$today);
        $dateParts[0] = ($dateParts[0] + $expirationYears);
        $expirationDate = implode('-',$dateParts);

        $entry = Express::buildEntry(self::aftmMembershipHandle);
        $entry->setMemberStartDate($today);
        $entry->setMemberRenewedDate($today);
        $entry->setMemberExpirationDate($expirationDate);
        $attributes=array(
            'member_first_name',
            'member_last_name',
            'member_invoice_number',
            'member_address1',
            'member_address2',
            'member_city',
            'member_state',
            'member_postal_code',
            'member_email',
            'membership_type',
            'member_band_name',
            'member_band_website',
            'member_volunteer_interest',
            'member_payment_method',
            'new_or_renewal',
            'member_ideas'

        );

        foreach ($attributes as $attribute) {
            if (isset($memberData[$attribute])) {
                $value = $memberData[$attribute];
                $entry->setAttribute($attribute,$value);
            }
        }

        $entry->save();

    }

    public static function AddMembership($memberData) {
        self::getInstance()->insertMembershipEntry($memberData);
    }
    public static function UpdatePayment($invoiceNumber) {
        self::getInstance()->updatePaymentInfo($invoiceNumber);
    }
}