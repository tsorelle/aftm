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
        /**
         * @var \Concrete\Core\Mail\Service
         * see https://documentation.concrete5.org/developers/sending-mail/working-mail-service
         */
        $mailService = Core::make('mail');
        $testing = AftmConfiguration::getValue('emailtesting','site');
        $mailService->setTesting($testing); // or true to throw an exception on error
        $mailService->addParameter('name','my name is mud');// $formData->member_first_name.' '.$formData->member_last_name);
        $mailService->load('form_member_ipn_confirm','aftm');
        $mailService->setSubject('Membership fee received');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');


        // $mailService->to($recipient);

        // $mailService->sendMail();

    }

    function updateData($inputs)
    {
        if (isset($inputs->invoice) && isset($inputs->invoice->invoicenumber) ) {
            if (!AftmMemberEntityManager::UpdatePayment($inputs->invoice->invoicenumber)) {
                $this->writeLog("Warning: No membership entry found for invoice number '$inputs->invoice->invoicenumber'.");
            }
        }
        else {
            $this->writeLog('Warning: No invoice number recieved in IpnControllerMembership::updateData.');
        }
    }

    function getFormId()
    {
        return "member";
    }
}