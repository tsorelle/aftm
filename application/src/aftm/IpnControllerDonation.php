<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/11/2017
 * Time: 6:08 PM
 */

namespace Application\aftm;


class IpnControllerDonation extends IpnControllerBase
{

    /**
     * @return \stdClass
     *
     * Example:
     * $result = new \stdClass();
     * $result->item_name = $this->getPostValue('item_name');
     * return $result
     */
    function getPostValues()
    {
        $result = new \stdClass();
        $result->warnings = array();
        $custom = $this->customValuesToArray();
        $formName = array_key_exists('formid',$custom) ? $custom['formid'] : '';
        if ($formName != $this->getFormId()) {
            $result->warnings[] = "Form id in request '$formName' does not match form id '".$this->getFormId()."'. Notify webmaster.";
        }
        
        $result->payer_firstname = $this->getPostValue('first_name','(not found)');
        $result->payer_lastname = $this->getPostValue('last_name','(not found)');
        $result->paypal_txn_id = $this->getPostValue('txn_id','(not found)');
        $result->payment_amount = $this->getPostValue('payment_gross');
        $result->payment_memo = $this->getPostValue('memo');
        $result->donor_name = array_key_exists('donorname',$custom) ? $custom['donorname'] : '(not found)';
        return $result;

    }

    /**
     * @param \stdClass $params
     *      with elements request, details, invoice
     * @return mixed
     */
    function sendNotifications($params)
    {
        // TODO: Implement sendNotifications() method.
        
        // send acknowledgement to donor
        
        // send notice to treasurer
    }

    /**
     * @param \stdClass $params
     *      with elements request, details, invoice
     * @return boolean
     */
    function updateData($inputs)
    {
        if (isset($inputs->invoice) && isset($inputs->invoice->invoicenumber) ) {
            $entry = AftmDonationEntityManager::UpdatePayment($inputs->invoice->invoicenumber,$inputs->request->payment_amount);
            if ($entry === false) {
                $message = "Warning: No membership entry found for invoice number '$inputs->invoice->invoicenumber'.";
                $this->addWarning( $message);
                $this->writeLog($message);
                return false;
            }
            $entry = Express::refresh($entry);
            $donation = new \stdClass();
            $donation->donor_first_name           = $entry->getAttribute('donor_first_name');
            $donation->donor_last_name            = $entry->getAttribute('donor_last_name');
            $donation->donor_address1             = $entry->getAttribute('donor_address1');
            $donation->donor_address2             = $entry->getAttribute('donor_address2');
            $donation->donor_city                 = $entry->getAttribute('donor_city');
            $donation->donor_state                = $entry->getAttribute('donor_state');
            $donation->donor_zipcode              = $entry->getAttribute('donor_postal_code');
            $donation->donor_email                = $entry->getAttribute('donor_email');
            $donation->donor_phone                = $entry->getAttribute('donor_phone');

            $inputs->$donation = $donation;
            return true;
        }
        else {
            $message = "Warning: No donation entry found for invoice number '$inputs->invoice->invoicenumber'.";
            $this->addWarning($message);
            $this->writeLog($message);
            return false;
        }
    }

    /**
     * @return string
     */
    function getFormId()
    {
        return 'donation';
    }
}