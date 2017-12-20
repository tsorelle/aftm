<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/11/2017
 * Time: 6:00 AM
 */

namespace Application\Block\ExternalForm\Form\Controller;

use Application\Aftm\AftmConfiguration;
use Application\Aftm\AftmDonationManager;
use Application\Aftm\AftmMailManager;
use Core;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Http\Request;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\PayPalForm;
use Application\Aftm\AftmCatalogManager;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Express;


class AftmMailForm extends AbstractController
{
    private $showCaptcha = false;

    private function setCaptcha()
    {
//        $setting = AftmConfiguration::getValue('captcha', 'form-mail');
//        $this->showCaptcha = (!empty($setting));
//        $this->set('showCaptcha', $this->showCaptcha);
        $this->set('showCaptcha', true);
    }

    private function getMailbox() {
        $mailbox = new \stdClass();
        $request = Request::getInstance();
        $mailbox->mailboxcode =   $request->get('mailbox');
        if (empty($mailbox->mailboxcode)) {
            $mailbox->mailboxcode = 'support';
        }

        $mailbox->displaytext=AftmConfiguration::getValue('title', "mailbox-$mailbox->mailboxcode");
        $mailbox->address=AftmConfiguration::getValue('address', "mailbox-$mailbox->mailboxcode");

        return $mailbox;
    }

    /**
     * Blank all data fields in form or return to defaults
     */
    private function clearFormData() {
        $formData = new \stdClass();
        $mailbox = $this->getMailbox();
        $formData->mailbox_code=$mailbox->mailboxcode;
        $formData->title=$mailbox->displaytext;
        $formData->to_address=$mailbox->address;

        $formData->from_name='';
        $formData->from_address='';
        $formData->subject='';
        $formData->message='';

        $this->set('formData',$formData);
    }
    private function setDefaults() {

        // todo: not needed?

    }

    /**
     * Controller 'view' action. Invoked before processing template ../aftm_donation_form.php
     */
    public function view()
    {
        $this->clearFormData();
        $this->setCaptcha();
        $this->setDefaults();
        $this->set('activepanel', 'mailform');
    }

    private function getRequestValues() {
        $formData = new \stdClass();
        $request = Request::getInstance();

        $textHelper = Core::make('helper/text');

        $formData->mailbox_code= $textHelper->sanitize($request->get('mailbox_code'));
        // $formData->mailbox_displaytext= $textHelper->sanitize($request->get(''));
        $formData->to_address= $textHelper->sanitize($request->get('to_address'));

        $formData->from_name= $textHelper->sanitize($request->get('from_name'));
        $formData->from_address= $textHelper->sanitize($request->get('from_address'));
        $formData->subject= $textHelper->sanitize($request->get('subject'));
        $formData->message= $textHelper->sanitize($request->get('message'));

        return $formData;
    }


    /**
     * Validate form values.
     *
     * @param $formData
     * @return bool - False on validation failure, True on success.
     */
    private function validate($formData) {

        $this->set('errormessage','');
        if ($this->showCaptcha) {
            $captcha = \Core::make("captcha");
            if (!$captcha->check()) {
                $this->set('errormessage', 'Please enter correct anti-spam "captcha" code. Scroll to end of the form. ');
                return false;
            }
        }

        if (empty($formData->from_name)) {
            $this->set('errormessage','You must enter  your email address.');
            return false;
        }
        if (empty($formData->from_address)) {
            $this->set('errormessage','You must enter  your email address.');
            return false;
        }

        if (empty($formData->subject)) {
            $this->set('errormessage','You must enter a subject.');
            return false;
        }
        if (empty($formData->message)) {
            $this->set('errormessage','You must enter a message.');
            return false;
        }

        return true;
    }

    /**
     * Entry point for form action.
     *
     * @param bool $bID
     * @return bool
     */
    public function action_submit_message($bID = false)
    {
        if ($this->bID == $bID) {
            $this->setDefaults();
            $this->setCaptcha();
            $formData = $this->getRequestValues();
            if (!$this->validate($formData)) {
                $this->set('formData',$formData);
                $this->set('activepanel','mailform');
                return false;
            }

            // send welcome message
            $mailManager = AftmMailManager::Create();
            $content = $mailManager->getTemplate('mailbox_contact_message.html',
                array(
                    'from_name' => $formData->from_name,
                    'from_address' => $formData->from_address,
                    'message' => $formData->message));

            $logoMarkup = $mailManager->getLogoMarkup();
            $plainLinks = $mailManager->getPlainLinks();
            $contentHtml = str_replace('[[links]]', $logoMarkup, $content);
            $contentHtml = $mailManager->mergeHtml($contentHtml);
            $contentText = $mailManager->toPlainText($content);
            $contentText = str_replace('[[links]]', $plainLinks, $contentText);

            /**
             * @var \Concrete\Core\Mail\Service
             * see https://documentation.concrete5.org/developers/sending-mail/working-mail-service
             */
            $mailService = Core::make('mail');
            // $mailService->setTesting($testing);
            $mailService->setBody($contentText);
            $mailService->setBodyHTML($contentHtml);
            $mailService->setSubject($formData->subject);
            $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
            $mailService->to($formData->to_address);
            $mailService->sendMail();

            $this->clearFormData();


            // $this->set('response', $message);
            return true;
        }

    }

}