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

    private function interestsToString($memberData) {
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
    public function updatePaymentInfo($invoiceNumber)
    {
        $entity = $this->getEntity();
        $list = new EntryList($entity);
        $list->filterByMemberInvoiceNumber($invoiceNumber);
        $results = $list->getResults();
        if (empty($results)) {
            return false;
        }
        $entry = $results[0];
        $today = date('Y-m-d');
        $entry->setAttribute('member_payment_received_date',$today);
        $entry->save();

        return true;
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
        $membershipType = isset($memberData->membership_type) ? $memberData->membership_type : '';
        $interests = $this->interestsToString($memberData);
        $expirationYears = $this->getMembershipTerm($membershipType);
        $today = date('Y-m-d');
        $dateParts = explode('-',$today);
        $dateParts[0] = ($dateParts[0] + $expirationYears);
        $expirationDate = implode('-',$dateParts);

        $entry = Express::buildEntry(self::aftmMembershipHandle);
        $entry->setMemberStartDate($today);
        $entry->setMemberRenewedDate($today);
        $entry->setMemberExpirationDate($expirationDate);

        $entry->setAttribute('member_first_name',         $memberData->member_first_name  );
        $entry->setAttribute('member_last_name',          $memberData->member_last_name   );
        $entry->setAttribute('member_invoice_number',     $memberData->invoicenumber      );
        $entry->setAttribute('member_address1',           $memberData->member_address1    );
        $entry->setAttribute('member_address2',           $memberData->member_address2    );
        $entry->setAttribute('member_city',               $memberData->member_city        );
        $entry->setAttribute('member_state',              $memberData->member_state       );
        $entry->setAttribute('member_postal_code',        $memberData->member_zipcode     );
        $entry->setAttribute('member_email',              $memberData->member_email       );
        $entry->setAttribute('membership_type',           $memberData->membership_type    );
        $entry->setAttribute('member_band_name',          $memberData->member_band_name   );
        $entry->setAttribute('member_band_website',       $memberData->member_band_website);
        $entry->setAttribute('member_volunteer_interest', $interests                      );
        $entry->setAttribute('member_payment_method',     $memberData->payment_method     );
        $entry->setAttribute('new_or_renewal',            $memberData->new_or_renewal     );
        $entry->setAttribute('member_ideas',              $memberData->member_ideas       );

        $entry->save();
    }

    public static function GetMemberInterests($memberData) {
        return self::getInstance()->interestsToString($memberData);
    }
    public static function AddMembership($memberData) {
        self::getInstance()->insertMembershipEntry($memberData);
    }
    public static function UpdatePayment($invoiceNumber) {
        return self::getInstance()->updatePaymentInfo($invoiceNumber);
    }
}