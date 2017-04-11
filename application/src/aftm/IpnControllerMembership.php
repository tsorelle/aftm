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
            $result->errors[] = "Form id in request '$formName' does not match form id '".$this->getFormId()."'. Notify webmaster.";
        }

        $result->firstname = $this->getPostValue('first_name','(not found)');
        $result->lastname = $this->getPostValue('last_name','(not found)');
        $result->paypal_txn_id = $this->getPostValue('txn_id','(not found)');
        $result->membershiptype = $this->getPostValue('option_selection1','(not found)');
        $result->amount = $this->getPostValue('payment_gross');
        $result->member_name = array_key_exists('membername',$custom) ? $custom['membername'] : '(not found)';
        $result->paypalmemo = $this->getPostValue('memo');

        $cost = AftmCatalogManager::GetPrice('membership',$result->membershiptype);
        if (empty($result->amount)) {
            $this->addWarning('PayPal did not return payment amound.');
            $result->amount = '(not found)';
        }
        else if ($result->amount != $cost) {
            $this->addWarning( "Cost in catalog ($cost) does not match payment amount ($result->amount)");
        }
        $result->cost = $cost;

        return $result;
    }

    /**
     * @param $mailManager AftmMailManager
     * @param $membership \stdClass
     * @return string
     */
    private function getContactInfo($mailManager, $membership) {
        $name = (isset($membership->firstname) ? $membership->lastname.' ' : '').
            (isset($membership->lastname) ? $membership->lastname : '');

        $contactInfo = $mailManager->formatAddressHtml(
            $membership->address1 ,
            $membership->address2 ,
            $membership->city ,
            $membership->state ,
            $membership->postalcode,
            $name);

        if (!empty($membership->phone)) {
            $contactInfo .= '<p>Phone: '.$membership->phone.'</p>';
        }

        if (!empty($membership->groupname)) {
            $contactInfo .= '<p>Group: '.$membership->groupname;
            if (!empty($membership->groupwebsite)) {
                $contactInfo .= ' ('.$membership->groupwebsite.')';
            }
            $contactInfo .= '</p>';
        }
        return $contactInfo;
    }


    function sendNotifications($inputs)
    {
        $enabled = AftmConfiguration::getValue('email', 'form-member');
        if (!$enabled) {
            return;
        }
        $testing = AftmConfiguration::getValue('emailtesting', 'site'); // if true, throw error exceptions

        $recipients = AftmConfiguration::getEmailValues('notifications2', 'form-member');
        if (empty($recipients)) {
            return;
        }

        $mailManager = AftmMailManager::Create();
        $invoiceNumber = $this->getInvoiceNumber();
        $warnings =  $this->getEmailWarnings();
        if (isset($inputs->membership) ) {
            $membershipType = isset($inputs->membership->membershiptype) ? $inputs->membership->membershiptype : '(not found)';
            $email = isset($inputs->membership->email) ? $inputs->membership->email : '(not found)';
            $contactInfo = $this->getContactInfo($mailManager,$inputs->membership);
        }
        else {
            $membershipType =  '(not found)';
            $contactInfo = '(not found)';
            $email = '(not found)';
        }

        $content = $mailManager->getTemplate('member_paypal_confirm.html',
            array(
                'contactInfo' => $contactInfo,
                'email' => $email,
                'membership' => $membershipType,
                'cost' => $inputs->request->cost,
                'invoice' => $invoiceNumber,
                'payorfirst' => $inputs->request->firstname,
                'payorlast' => $inputs->request->lastname,
                'txnid' => $inputs->request->paypal_txn_id,
                'pmtamount' => $inputs->request->amount,
                'ppmembership' => $inputs->request->membershiptype,
                'ppmembername' => $inputs->request->member_name,
                'warnings' => $warnings
            ));

        $contentHtml = $mailManager->mergeHtml($content);
        $contentText = $mailManager->toPlainText($content);

        $mailService = Core::make('mail');
        $mailService->setTesting($testing); // or true to throw an exception on error
        $mailService->setSubject('PayPal membership payment confirmed');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        foreach ($recipients as $recipient) {
            $mailService->to($recipient);
        }
        $mailService->setBody($contentText);
        $mailService->setBodyHTML($contentHtml);
        $mailService->sendMail();

        if (!empty($warnings)) {
            $mailService = Core::make('mail');
            $mailService->setTesting($testing);
            $mailService->setSubject('Warnings in PayPal confirmation');
            $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
            $mailService->to('websupport@aftm.us');
            $mailService->setBody($contentText);
            $mailService->setBodyHTML($contentHtml);
            $mailService->sendMail();
        }
    }

    function updateData($inputs)
    {
        if (isset($inputs->invoice) && isset($inputs->invoice->invoicenumber) ) {
            $inputs->membership = AftmMembershipManager::UpdatePayment($inputs->invoice->invoicenumber,$inputs->invoice->cost,$inputs->invoice->paypalmemo);
            if ($inputs->membership === false) {
                $message = "Warning: No membership entry found for invoice number '$inputs->invoice->invoicenumber'.";
                $this->addWarning( $message);
                $this->writeLog($message);
                return false;
            }
            return true;
        }
        else {
            $message = "Warning: No membership entry found for invoice number '$inputs->invoice->invoicenumber'.";
            $this->addWarning($message);
            $this->writeLog($message);
            return false;
        }
    }

    function getFormId()
    {
        return "member";
    }
}