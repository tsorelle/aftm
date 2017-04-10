<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 9:12 AM
 */

namespace Application\Aftm\forms;


use Application\Aftm\AftmCatalogManager;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMembershipManager;
use PDO;

class MembershipFormHelper
{
    private $membershipTypes;
    public function getMembershipTypes()
    {
        return $this->membershipTypes;
    }

    public function __construct()
    {

        // Important! values must match those defined in the PayPal hosted form.
        // See \application\src\aftm\config.ini [form-member] for hosted button id numbers.
        $this->membershipTypes = AftmCatalogManager::GetSelectList('membership', "--- Select ---");
    }

    private function getInvoiceAddress($formData) {
        $result = '';
        if (!empty($formData->member_address1)) {
            $result = $formData->member_address1;
        }
        if (!empty($formData->member_address2)) {
            $result .= (empty($result) ? '' : ',').$formData->member_address2;
        }
        $city = trim($formData->member_city.' '.$formData->member_state.' '.$formData->member_zipcode);
        if (!empty($city)) {
            $result .= (empty($result) ? '' : ',') . $city;
        }
        return $result;
    }

    public function GetCost($membershipType) {
        if (!key_exists($membershipType, $this->membershipTypes)) {
            return false;
        }
        $result = $this->membershipTypes[$membershipType];
        $parts = explode('$',$result);
        if (sizeof($parts) < 2) {
            throw new \Exception('Membership description must end with price, proceded by a dollar sign');
        }
        return trim($parts[1]);
    }

    public function PostInvoice($formData) {
        $invoiceData = array(
            'customername'    => $formData->member_first_name.' '.$formData->member_last_name,
            'customeraddress' => $this->getInvoiceAddress($formData),
            'customeremail'   => $formData->member_email,
            'paymentmethod' => $formData->payment_method
        );

        $invoiceItems = Array (
            Array(
                'itemname'  => 'membership',
                'itemtype'  => $formData->membership_type,
                'quantity'  => '1',
                'amount'    => $formData->cost,
            )
        );
        $id = AftmInvoiceManager::Post($invoiceData,$invoiceItems);
        return $id;
    }

    public function AddMembership(&$formData) {
        $formData->cost = $this->GetCost($formData->membership_type);
        $formData->invoicenumber = $this->PostInvoice($formData);
        AftmMembershipManager::AddMembership($formData);
    }
}