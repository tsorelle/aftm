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

class AftmDonationEntityManager
{
    const aftmDonationHandle = 'aftm_donation';

    private static $instance;
    private static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new AftmDonationEntityManager();
        }
        return self::$instance;
    }

    public function getEntity() {
        $entity = Express::getObjectByHandle(self::aftmDonationHandle);
        if (empty($entity)) {
            throw new \Exception('Entity "AFTM Donation" does not exist');
        }
        return $entity;
    }

    public function getDonationByInvoiceNumber($invoiceNumber) {
        $entity = $this->getEntity();
        $list = new EntryList($entity);
        $list->filterByDonationInvoiceNumber($invoiceNumber);
        $results = $list->getResults();
        if (empty($results)) {
            return false;
        }
        return $results[0];
    }
    public function updatePaymentInfo($invoiceNumber,$amount,$memo='')
    {
        $entity = $this->getEntity();
        $list = new EntryList($entity);
        $list->filterByDonationInvoiceNumber($invoiceNumber);
        $results = $list->getResults();
        if (empty($results)) {
            return false;
        }
        $entry = $results[0];

        $today = date('Y-m-d');
        $entry->setAttribute('donation_received_date',$today);
        $entry->setAttribute('donation_amount',$amount);
        $entry->setAttribute('donation_memo',$memo);
        $entry->save();

        return $entry;
    }


    public function insertDonationEntry($donationData) {
        $entry = Express::buildEntry(self::aftmDonationHandle);
        $entry->setAttribute('donor_first_name',         $donationData->donor_first_name  );
        $entry->setAttribute('donor_last_name',          $donationData->donor_last_name   );
        $entry->setAttribute('donation_invoice_number',  $donationData->donation_invoice_number );
        $entry->setAttribute('donor_address1',           $donationData->donor_address1    );
        $entry->setAttribute('donor_address2',           $donationData->donor_address2    );
        $entry->setAttribute('donor_city',               $donationData->donor_city        );
        $entry->setAttribute('donor_state',              $donationData->donor_state       );
        $entry->setAttribute('donor_postal_code',        $donationData->donor_zipcode     );
        $entry->setAttribute('donor_email',              $donationData->donor_email       );
        $entry->setAttribute('donor_phone',              $donationData->donor_phone       );

        $entry->save();
    }

    public static function AddDonation($donationData) {
        self::getInstance()->insertDonationEntry($donationData);
    }
    public static function UpdatePayment($invoiceNumber,$amount,$memo='') {
        return self::getInstance()->updatePaymentInfo($invoiceNumber,$amount,$memo);
    }

}