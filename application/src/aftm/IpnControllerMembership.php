<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 12/29/2016
 * Time: 7:06 AM
 */
/**
 * Must declare route in \application\bootstrap\app.php
 *
 * Route::register(
 *      '/tops/service/execute/{arg}',
 *      'Concrete\Package\Tops\Controller\ServiceRequestHandler::executeService'
 *      );
 *
 */

namespace Application\Aftm;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Express\Entry\Search\Result\Result;
use Concrete\Core\Express\EntryList;



class IpnControllerMembership extends IpnControllerBase
{

    function getPostValues()
    {
        $result = new \stdClass();
        $result->warnings = array();
        $custom = $this->customValuesToArray();
        $formName = array_key_exists('formid',$custom) ? $custom['formid'] : '';
        if ($formName != $this->getFormId()) {
            $result->warnings[] = "Warning: form id in request '$formName' does not match form id '".$this->getFormId()."'. Notify webmaster.";
        }

        $result->payer_name = $this->getPostValue('first_name').' '.$this->getPostValue('last_name');
        $result->paypal_txn_id = $this->getPostValue('txn_id');
        $result->membership_type = $this->getPostValue('option_selection1');
        $result->payment_amount = $this->getPostValue('payment_gross');
        $result->member_name = array_key_exists('membername',$custom) ? $custom['membername'] : '';

        $cost = AftmCatalogManager::GetPrice('membership',$result->membership_type);
        if ($result->payment_amount != $cost) {
            $result->warnings[] = "Warning: Cost in catalog ($cost) does not match payment amount ($result->payment_amount)";
        }

        return $result;

    }

    function sendNotifications($inputs)
    {
        // TODO: Implement sendNotifications() method.
    }

    function updateData($inputs)
    {
        // TODO: Implement updateData() method.
    }

    function getFormId()
    {
        return "member";
    }

    /**
     * Get transaction details from data
     *
     * @param array $inputs
     * @return \stdClass
     */
    function getDetails($inputs)
    {
        // TODO: Implement getDetails() method.
    }
}