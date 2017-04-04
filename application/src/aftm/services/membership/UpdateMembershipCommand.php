<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 3:37 PM
 */

namespace Application\Aftm\services\membership;


use Application\Aftm\AftmCatalogManager;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMembershipManager;
use Application\Tops\services\TServiceCommand;

class UpdateMembershipCommand  extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('memberships.edit');
    }


    private function getInvoiceAddress($membership) {
        $result = '';
        if (!empty($membership->address1)) {
            $result = $membership->address1;
        }
        if (!empty($membership->address2)) {
            $result .= (empty($result) ? '' : ',').$membership->address2;
        }
        $city = trim($membership->city.' '.$membership->state.' '.$membership->postalcode);
        if (!empty($city)) {
            $result .= (empty($result) ? '' : ',') . $city;
        }
        return $result;
    }

    private function getAmmount(&$membership) {
        if (empty($membership->amount)) {
            $membership->amount = AftmCatalogManager::GetPrice('membership',$membership->membershiptype);
        }
        return $membership->amount;
    }

    private function postInvoice(&$membership) {
        $paid = (!empty($membership->amount));

        $invoiceData = array(
            'customername'    => $membership->firstname.' '.$membership->lastname,
            'customeraddress' => $this->getInvoiceAddress($membership),
            'customeremail'   => $membership->email,
            'paymentmethod' =>   empty($membership->paymentmethod) ? 'check' : $membership->paymentmethod,
            'paid' => $paid ?  1 : 0
        );
        if ($paid) {
            $invoiceData['paiddate'] = empty($membership->paymentreceiveddate) ? date("Y-m-d") : $membership->paymentreceiveddate;
        }

        $this->getAmmount($membership);

        $invoiceItems = Array (
            Array(
                'itemname'  => 'membership',
                'itemtype'  => 'general',
                'quantity'  => '1',
                'amount'    =>  empty($membership->amount) ? 0.0 : $membership->amount
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
        if (!isset($request->membership)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        $year = (empty($request->year)) ? null : $request->year;
        if ($year != null && !is_numeric($year)) {
            $year = null;
        }
        if (empty($request->membership->startdate)) {
            if (empty($request->membership->reneweddate)) {
                $request->membership->startdate = date('Y-m-d');
            }
            else {
                $request->membership->startdate = $request->membership->reneweddate;
            }
        }
        if (empty($request->membership->reneweddate)) {
            $request->membership->reneweddate = $request->membership->startdate;
        }

        $isnew = empty($request->membership->id);
        if ($isnew) {
            $request->membership->membershipnumber = $this->postInvoice($request->membership);
            AftmMembershipManager::NewMembership($request->membership);
            // if membership year differs from filter, change it to year of new membership
            if ($year != null) {
                $year = null;
                if (!empty($request->membership->reneweddate)) {
                    $time = strtotime($request->membership->reneweddate);
                    if (!empty($time)) {
                        $year = date("Y", $time);
                    }
                }
            }
        }
        else {
            AftmMembershipManager::UpdateMembership($request->membership);
        }
        $result = new \stdClass();
        $result->memberships = AftmMembershipManager::GetMembershipList($year);
        $result->yearlist = AftmMembershipManager::GetMembershipYearList();
        $result->year = $year;
        $this->setReturnValue($result);
    }
}