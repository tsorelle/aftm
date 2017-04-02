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

        $result->payer_firstname = $this->getPostValue('first_name','(not found)');
        $result->payer_lastname = $this->getPostValue('last_name','(not found)');
        $result->paypal_txn_id = $this->getPostValue('txn_id','(not found)');
        $result->membership_type = $this->getPostValue('option_selection1','(not found)');
        $result->payment_amount = $this->getPostValue('payment_gross');
        $result->member_name = array_key_exists('membername',$custom) ? $custom['membername'] : '(not found)';
        $result->payment_memo = $this->getPostValue('memo');

        $cost = AftmCatalogManager::GetPrice('membership',$result->membership_type);
        if (empty($result->payment_amount)) {
            $this->addWarning('PayPal did not return payment amound.');
            $result->payment_amount = '(not found)';
        }
        else if ($result->payment_amount != $cost) {
            $this->addWarning( "Cost in catalog ($cost) does not match payment amount ($result->payment_amount)");
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
        $name = (isset($membership->member_first_name) ? $membership->member_first_name.' ' : '').
            (isset($membership->member_last_name) ? $membership->member_last_name : '');
        $contactInfo = $mailManager->formatAddressHtml(
            $membership->member_address1 ,
            $membership->member_address2 ,
            $membership->member_city ,
            $membership->member_state ,
            $membership->member_zipcode,
            $name);

        if (!empty($membership->member_band_name)) {
            $contactInfo .= '<p>Group: '.$membership->member_band_name;
            if (!empty($membership->member_band_website)) {
                $contactInfo .= ' ('.$membership->member_band_website.')';
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
            $membershipType = isset($inputs->membership->membership_type) ? $inputs->membership->membership_type : '(not found)';
            $email = isset($inputs->membership->member_email) ? $inputs->membership->member_email : '(not found)';
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
                // 'membership' => $inputs->request->membership_type,
                'cost' => $inputs->request->cost,
                'invoice' => $invoiceNumber,
                'payorfirst' => $inputs->request->payer_firstname,
                'payorlast' => $inputs->request->payer_lastname,
                'txnid' => $inputs->request->paypal_txn_id,
                'pmtamount' => $inputs->request->payment_amount,
                'ppmembership' => $inputs->request->membership_type,
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
            // todo: use database object
            $entry = AftmMembershipManager::UpdatePayment($inputs->invoice->invoicenumber,$inputs->invoice->cost,$inputs->invoice->payment_memo);
            if ($entry === false) {
                $message = "Warning: No membership entry found for invoice number '$inputs->invoice->invoicenumber'.";
                $this->addWarning( $message);
                $this->writeLog($message);
                return false;
            }
            $entry = Express::refresh($entry);
            $membership = new \stdClass();
            $membership_type             = $entry->getAttributeValueObject('membership_type');
            $membership->membership_type             = $membership_type->getPlainTextValue();
            $membership->new_or_renewal              = $entry->getAttribute('new_or_renewal');
            $membership->member_first_name           = $entry->getAttribute('member_first_name');
            $membership->member_last_name            = $entry->getAttribute('member_last_name');
            $membership->member_address1             = $entry->getAttribute('member_address1');
            $membership->member_address2             = $entry->getAttribute('member_address2');
            $membership->member_city                 = $entry->getAttribute('member_city');
            $membership->member_state                = $entry->getAttribute('member_state');
            $membership->member_zipcode              = $entry->getAttribute('member_postal_code');
            $membership->member_email                = $entry->getAttribute('member_email');
            $membership->member_band_name            = $entry->getAttribute('member_band_name');
            $membership->member_band_website         = $entry->getAttribute('member_band_website');
            // not used
            // $membership->payment_method              = $entry->getAttribute('payment_method');
            // $membership->member_volunteer_interest   = $entry->getAttribute('member_volunteer_interest');
            // $membership->member_ideas                = $entry->getAttribute('member_ideas');
            $inputs->membership = $membership;
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