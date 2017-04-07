<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 6:00 PM
 */

namespace Application\Aftm\services\donations;


use Application\Aftm\AftmDonationManager;
use Application\Aftm\AftmInvoiceManager;
use Application\Tops\services\TServiceCommand;

class UpdateDonationCommand extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('donations.edit');
    }


    private function getInvoiceAddress($donation) {
        $result = '';
        if (!empty($donation->address1)) {
            $result = $donation->address1;
        }
        if (!empty($donation->address2)) {
            $result .= (empty($result) ? '' : ',').$donation->address2;
        }
        $city = trim($donation->city.' '.$donation->state.' '.$donation->postalcode);
        if (!empty($city)) {
            $result .= (empty($result) ? '' : ',') . $city;
        }
        return $result;
    }


    private function postInvoice($donation) {
        $paid = (!empty($donation->amount));

        $invoiceData = array(
            'customername'    => $donation->firstname.' '.$donation->lastname,
            'customeraddress' => $this->getInvoiceAddress($donation),
            'customeremail'   => $donation->email,
            'paymentmethod' =>   empty($donation->paymentmethod) ? 'check' : $donation->paymentmethod,
            'paid' => $paid ?  1 : 0
        );
        if ($paid) {
            $invoiceData['paiddate'] = empty($donation->datereceived) ? date("Y-m-d") : $donation->datereceived;
        }

        $invoiceItems = Array (
            Array(
                'itemname'  => 'donation',
                'itemtype'  => 'general',
                'quantity'  => '1',
                'amount'    =>  empty($donation->amount) ? 0.0 : $donation->amount
            )
        );
        $id = AftmInvoiceManager::Post($invoiceData,$invoiceItems);
        return $id;
    }
    
    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        if (!isset($request->donation)) {
            $this->addErrorMessage('No donation object received.');
            return;
        }
        $year = (empty($request->year)) ? null : $request->year;
        if ($year != null && !is_numeric($year)) {
            $year = null;
        }
        $isnew = empty($request->donation->id);
        if ($isnew) {
            $request->donation->donationnumber = $this->postInvoice($request->donation);
            AftmDonationManager::NewDonation($request->donation);

        }
        else {
            AftmDonationManager::UpdateDonation($request->donation);
        }

        // if donation year differs from filter, change it to year of new donation
        if ($year != null) {
            $year = null;
            if (!empty($request->donation->datereceived)) {
                $time = strtotime($request->donation->datereceived);
                if (!empty($time)) {
                    $year = date("Y", $time);
                }
            }
        }

        $result = new \stdClass();
        $result->donations = AftmDonationManager::GetDonationList($year);
        $result->yearlist = AftmDonationManager::GetDonationYearList();
        $result->year = $year;
        $this->setReturnValue($result);
    }
}