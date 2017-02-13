<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/11/2017
 * Time: 6:08 PM
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
    function sendNotifications($inputs)
    {
        $enabled = AftmConfiguration::getValue('email', 'form-donation');
        if (!$enabled) {
            return;
        }
        $testing = AftmConfiguration::getValue('emailtesting', 'site'); // if true, throw error exceptions
        $mailManager = AftmMailManager::Create();

        $donorname = $inputs->donation->donor_first_name . ' ' . $inputs->donation->donor_last_name;
        $contactInfo = $mailManager->formatAddressHtml(
            $inputs->donation->donor_address1 ,
            $inputs->donation->donor_address2 ,
            $inputs->donation->donor_city ,
            $inputs->donation->donor_state ,
            $inputs->donation->donor_zipcode,
            $donorname);

        $donorEmail = $inputs->donation->donor_email;

        if (empty($inputs->request->payment_memo)) {
            $memo = '';
        }
        else {
            $memo = '<p><b>The donor entered the following message in PayPal:</b><br>'."\n".
                $inputs->request->payment_memo.'</p>'."\n";
        }

        if (empty($inputs->donor->donor_phone)) {
            $phone = '';
        }
        else {
            $phone = '<p><b>Phone:</b>'.$inputs->donor->donor_phone.'</p>'; // for treasurer message
        }


        if (empty($donorEmail)) {
            $emailaddress = "<p>Email address was not provided. Please acknowlege by postal mail."; // for treasurer message
        }
        else {
            $emailaddress =  "<p><b>Email address:</b> $donorEmail</p>"; // for treasurer message


            // send acknowledgement to donor
            $content = $mailManager->getTemplate('donation_acknowledge.html',
                array(
                    'amount'          => $inputs->request->payment_amount,
                    'donorname'       => $donorname,
                    'contactInfo'     => $contactInfo,
                    'invoice'    => $inputs->invoice->invoicenumber
                ));

            $logoMarkup = $mailManager->getLogoMarkup('site,contact,join');
            $plainLinks = $mailManager->getPlainLinks('site,contact,join');
            $contentHtml = str_replace('[[links]]', $logoMarkup, $content);
            $contentHtml = $mailManager->mergeHtml($contentHtml);
            $contentText = $mailManager->toPlainText($content);
            $contentText = str_replace('[[links]]', $plainLinks, $contentText);

            /**
             * @var \Concrete\Core\Mail\Service
             * see https://documentation.concrete5.org/developers/sending-mail/working-mail-service
             */
            $mailService = Core::make('mail');
            $mailService->setTesting($testing);
            $mailService->setBody($contentText);
            $mailService->setBodyHTML($contentHtml);
            $mailService->setSubject('Thank you from AFTM');
            $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
            $mailService->to($donorEmail);
            $mailService->sendMail();

        }

        // send notice to treasurer
        $recipients = AftmConfiguration::getEmailValues('notifications1', 'form-donation');
        if (empty($recipients)) {
            return;
        }

        $warnings = $this->getEmailWarnings();

        $content = $mailManager->getTemplate('donation_notify.html',
            array(
                'amount'          => $inputs->request->payment_amount,
                'donorname'       => $donorname,
                'memo'            => $memo,
                'contactInfo'     => $contactInfo,
                'emailaddress'    => $emailaddress,
                'phone' => $phone,
                'invoice'         => $inputs->invoice->invoicenumber,
                'payorfirst'      => $inputs->request->payer_firstname,
                'payorlast'       => $inputs->request->payer_lastname,
                'txnid'           => $inputs->request->paypal_txn_id,
                'warnings'        => $warnings
            ));

        $contentText = $mailManager->toPlainText($content);

        $mailService = Core::make('mail');
        $mailService->setTesting($testing);
        $contentHtml = $mailManager->mergeHtml($content);
        $contentText = $mailManager->toPlainText($content);
        $mailService->setBodyHTML($contentHtml);
        $mailService->setBody($contentText);
        $mailService->setSubject('AFTM Donation received');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        foreach ($recipients as $recipient) {
            $mailService->to($recipient);
        }
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
                $message = "Warning: No donation entry found for invoice number '$inputs->invoice->invoicenumber'.";
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
            $donation->donor_phone                = $entry->getAttribute('donor_phone');
            $donation->donor_email = $entry->getAttribute('donor_email');


            $inputs->donation = $donation;
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