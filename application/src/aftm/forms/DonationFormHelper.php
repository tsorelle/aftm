<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 3:25 PM
 */

namespace Application\Aftm\forms;


use Application\Aftm\AftmDonationManager;
use Application\Aftm\AftmInvoiceManager;

class DonationFormHelper
{
    private function getInvoiceAddress($formData) {
        $result = '';
        if (!empty($formData->donor_address1)) {
            $result = $formData->donor_address1;
        }
        if (!empty($formData->donor_address2)) {
            $result .= (empty($result) ? '' : ',').$formData->donor_address2;
        }
        $city = trim($formData->donor_city.' '.$formData->donor_state.' '.$formData->donor_zipcode);
        if (!empty($city)) {
            $result .= (empty($result) ? '' : ',') . $city;
        }
        return $result;
    }


    private function postInvoice($formData) {
        $invoiceData = array(
            'customername'    => $formData->donor_first_name.' '.$formData->donor_last_name,
            'customeraddress' => $this->getInvoiceAddress($formData),
            'customeremail'   => $formData->donor_email,
            'paymentmethod' => 'paypal'
        );

        $invoiceItems = Array (
            Array(
                'itemname'  => 'donation',
                'itemtype'  => 'general',
                'quantity'  => '1',
                'amount'    => 0.0,
            )
        );
        $id = AftmInvoiceManager::Post($invoiceData,$invoiceItems);
        return $id;
    }


    public function AddDonation(&$formData) {
        $formData->donation_invoice_number = $this->postInvoice($formData);
        AftmDonationManager::AddDonation($formData);
    }
}