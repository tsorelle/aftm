<?php
namespace Application\Block\ExternalForm\Form\Controller;

use Application\Aftm\AftmMailManager;
use Application\Aftm\forms\MembershipFormHelper;
use Core;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Http\Request;
use Application\Aftm\AftmConfiguration;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMembershipManager;
use Application\Aftm\PayPalForm;
use Application\Aftm\AftmCatalogManager;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Express;

/**
 * Class AftmMemberForm
 * @package Application/Block/ExternalForm/Form/Controller
 *
 * Form controller for membership form
 * see /application/src/aftm/doc/member-form.txt
 */
class AftmMemberForm extends AbstractController
{

    /**
     * @var $helper MembershipFormHelper
     */
    private $helper;

    /**
     * @return MembershipFormHelper
     */
    private function getHelper()
    {
        if (!isset($this->helper)) {
            $this->helper = new MembershipFormHelper();
        }
        return $this->helper;
    }



    // private $showCaptcha = true; // set false for test sessions
    private $showCaptcha = false;
        /* =  array(
        "" => "--- Select ---",
        "Student 1-year" => "Student 1-year - $15.00",
        "Individual 1-year" => "Individual 1-year - $20.00",
        "Family 1-year" => "Family 1-year - $25.00",
        "Band or Dance Group 1-year" => "Band or Dance Group - 1-year $25.00",
        "Business 1-year" => "Business - 1-year $50.00",
        "Individual 5-year" => "Individual  - 5-year $80.00",
        "Family 5-year" => "Family - 5-year - $100.00",
        "Lifetime membership" => "Lifetime membership - $300.00");
        */

    /**
     * Retrieve and sanitize form values from request.
     *
     * @return \stdClass
     */
    private function getRequestValues() {
        $formData = new \stdClass();
        $request = Request::getInstance();

        $textHelper = Core::make('helper/text');

        $formData->new_or_renewal            = $textHelper->sanitize($request->get('new_or_renewal'));
        $formData->payment_method            = $textHelper->sanitize($request->get('payment_method'));
        $formData->member_first_name         = $textHelper->sanitize($request->get('member_first_name'));
        $formData->member_last_name          = $textHelper->sanitize($request->get('member_last_name'));
        $formData->member_address1           = $textHelper->sanitize($request->get('member_address1'));
        $formData->member_address2           = $textHelper->sanitize($request->get('member_address2'));
        $formData->member_city               = $textHelper->sanitize($request->get('member_city'));
        $formData->member_state              = $textHelper->sanitize($request->get('member_state'));
        $formData->member_zipcode            = $textHelper->sanitize($request->get('member_zipcode'));
        $formData->member_phone              = $textHelper->sanitize($request->get('member_phone'));
        $formData->member_email              = $textHelper->sanitize($request->get('member_email'));
        $formData->membership_type           = $textHelper->sanitize($request->get('membership_type'));
        $formData->member_band_name          = $textHelper->sanitize($request->get('member_band_name'));
        $formData->member_band_website       = $textHelper->sanitize($request->get('member_band_website'));
        $formData->member_ideas              = $textHelper->sanitize($request->get('member_ideas'));
        $formData->volunteer = new \stdClass();
        $formData->volunteer->concerts	 = $request->get('vol_concerts' ) ? true : false;
        $formData->volunteer->newsletter = $request->get('vol_newsletter') ? true : false;
        $formData->volunteer->publicity  = $request->get('vol_publicity') ? true : false;
        $formData->volunteer->festivals  = $request->get('vol_festivals') ? true : false;
        $formData->volunteer->membership = $request->get('vol_membership') ? true : false;
        $formData->volunteer->mailings   = $request->get('vol_mailings' ) ? true : false;
        $formData->volunteer->webpage    = $request->get('vol_webpage'  ) ? true : false;

        return $formData;
    }

    /**
     * Blank all data fields in form or return to defaults
     */
    private function clearFormData() {
        $formData = new \stdClass();
        $formData->new_or_renewal              = 'new';
        $formData->payment_method              = 'paypal';
        $formData->member_first_name           = '';
        $formData->member_last_name            = '';
        $formData->member_address1             = '';
        $formData->member_address2             = '';
        $formData->member_city                 = '';
        $formData->member_state                = '';
        $formData->member_zipcode              = '';
        $formData->member_email                = '';
        $formData->member_phone                = '';
        $formData->membership_type             = '';
        $formData->member_band_name            = '';
        $formData->member_band_website         = '';
        $formData->member_volunteer_interest   = '';
        $formData->member_ideas                = '';
        $formData->volunteer = new \stdClass();
        $formData->volunteer->concerts	 = false;
        $formData->volunteer->newsletter = false;
        $formData->volunteer->publicity  = false;
        $formData->volunteer->festivals  = false;
        $formData->volunteer->membership = false;
        $formData->volunteer->mailings   = false;
        $formData->volunteer->webpage    = false;

        $this->set('formData',$formData);
    }

    /**
     * Create markup for PayPalForm
     * See \application\src\aftm\config.ini [form-member] for hosted button id numbers and other values
     *
     * @param $memberType  - Must be one of the values defined in the hosted form on paypal
     * @param $invoicenumber - passed to paypal as unique invoice identifier
     * @param $customValue - passed to paypal as custom id value for member
     */
    private function getPayPalForm($memberType,$invoicenumber,$memberName) {
        $form = PayPalForm::CreateStoredForm('member');
        $ipnenabled = AftmConfiguration::getValue('ipnenabled','form-member',false);
        if ($ipnenabled) {
            $form->setIpnListner();
        }
        $form->addCustomValue("formid",'member');
        $form->addCustomValue("membername",$memberName);
        $form->setSelectedItem('Membership Type',$memberType);
        $form->setInvoiceNumber($invoicenumber);
        // $form->setCustomValue($customValue);
        $form->setReturnUrl();
        $autolaunch = AftmConfiguration::getValue('paypalredirect','form-member',true);
        $results = $form->getMarkup($autolaunch); // with 'autolaunch' immediate redirect to PayPal
        $this->set('paypalform',$results);
    }


    /**
     * @param $mailManager AftmMailManager
     * @param $formData
     * @return string
     */
    private function getContactInfo($mailManager, $formData) {
        $contactInfo = $mailManager->formatAddressHtml(
            $formData->member_address1 ,
            $formData->member_address2 ,
            $formData->member_city ,
            $formData->member_state ,
            $formData->member_zipcode);

        if (!empty($formData->member_phone)) {
            $contactInfo .= '<p>Phone: '.$formData->member_phone.'</p>';
        }

        if (!empty($formData->member_band_name)) {
            $contactInfo .= '<p>Group: '.$formData->member_band_name;
            if (!empty($formData->member_band_website)) {
                $contactInfo .= ' ('.$formData->member_band_website.')';
            }
            $contactInfo .= '</p>';
        }
        return $contactInfo;
    }

    private function getCheckInfo($formData)
    {
        if ($formData->payment_method != 'check') {
            return '';
        }
        else {
            $result = '<p>Please mail your check or money order for $' .
                $formData->cost.
                ' to:<br><br>Austin Friends of Traditional Music<br>P.O. Box 49608<br>Austin, TX 78765.</p>' .
                "<p></p>Write 'membership fee: $formData->membership_type' on the check memo and be sure that the name "  .
                'and email address you entered is written on the check or in an accompanying note. </p>';
        }
        return $result;
    }

    private function getAdditionalInfoSection($formData) {
        $memberInterests = AftmMembershipManager::GetMemberInterests($formData);
        if (empty( $formData->member_ideas) && empty($memberInterests) ) {
            return '';
        }
        $result = array('<h3>Additional Information</h3>');
        if (!empty($memberInterests)) {
            $result[] = "<p><b>Volunteer interests: </b> $memberInterests</p>";
        }
        if (!empty( $formData->member_ideas)) {
            $result[] = "<p><b>Ideas for AFTM:</b></p>";
            $result[] = "<p>$formData->member_ideas</p>";
        }
        return implode("\n",$result);
    }

    /**
     * Send email notifications before paypal submission
     *
     * @param $formData  - from getRequestValues()
     */
    private function sendNotifications($formData)
    {

        $enabled = AftmConfiguration::getValue('email', 'form-member');
        if (!$enabled) {
            return;
        }

        $testing = AftmConfiguration::getValue('emailtesting', 'site'); // if true, throw error exceptions

        // send welcome message
        $mailManager = AftmMailManager::Create();
        $contactInfo = $this->getContactInfo($mailManager, $formData);
        $membershipType = $formData->membership_type . ($formData->new_or_renewal == 'new' ? '' : ' (renewal)');
        $checkInfo = $this->getCheckInfo($formData);
        $content = $mailManager->getTemplate('welcome_member.html',
            array('membername' => $formData->member_first_name . ' ' . $formData->member_last_name,
                'cost' => $formData->cost,
                'checkInfo' => $checkInfo,
                'contactInfo' => $contactInfo,
                'membership' => $membershipType));

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
        $mailService->setTesting($testing);
        $mailService->setBody($contentText);
        $mailService->setBodyHTML($contentHtml);
        $mailService->setSubject('Welcome to AFTM');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        $mailService->to($formData->member_email);
        $mailService->sendMail();

        // send notification message
        $recipients = AftmConfiguration::getEmailValues('notifications1', 'form-member');
        if (empty($recipients)) {
            return;
        }

        $additional = $this->getAdditionalInfoSection($formData);
        $content = $mailManager->getTemplate('member_notify.html',
            array('membername' => $formData->member_first_name . ' ' . $formData->member_last_name,
                'cost' => $formData->cost,
                'payment-method' => $formData->payment_method,
                'contactInfo' => $contactInfo,
                'additional' => $additional,
                'email' => $formData->member_email,
                'phone' => $formData->member_phone,
                'invoice' => $formData->invoicenumber,
                'membership' => $membershipType));

        $contentHtml = str_replace('[[links]]', $logoMarkup, $content);
        $contentHtml = $mailManager->mergeHtml($contentHtml);
        $contentText = $mailManager->toPlainText($content);
        $contentText = str_replace('[[links]]', $plainLinks, $contentText);

        $mailService = Core::make('mail');
        $mailService->setTesting($testing); // or true to throw an exception on error
        $mailService->setSubject('Membership recieved');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        foreach ($recipients as $recipient) {
            $mailService->to($recipient);
        }
        $mailService->setBody($contentText);
        $mailService->setBodyHTML($contentHtml);
        $mailService->sendMail();

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
        if (empty($formData->membership_type)) {
            $this->set('errormessage','Please select a membership type');
            return false;
        }

        if (empty($formData->member_first_name) || empty($formData->member_last_name)) {
            $this->set('errormessage','You must enter first and last name');
            return false;
        }
        if (empty($formData->member_address1) ||
            empty($formData->member_city) ||
            empty($formData->member_state) ||
            empty($formData->member_zipcode)) {
            $this->set('errormessage','Please enter full address including address, city, state/province, postal code or country');
            return false;
        }
        if (empty($formData->member_email)) {
            $this->set('errormessage','Please enter your email address.');
            return false;
        }
        if (!filter_var($formData->member_email, FILTER_VALIDATE_EMAIL)) {
            $this->set('errormessage', 'Please enter a valid email address.');
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
    public function action_submit_member($bID = false)
    {
        if ($this->bID == $bID) {
            $this->helper = new MembershipFormHelper();
            $this->setDefaults();
            $this->setCaptcha();
            $formData = $this->getRequestValues();
            if (!$this->validate($formData)) {
                $this->set('formData',$formData);
                $this->set('activepanel','memberform');
                return false;
            }
            $this->getHelper()->AddMembership($formData);
            $memberName = $formData->member_first_name.' '.$formData->member_last_name;
            $formData->memberId = $memberName;
            if ($formData->payment_method == 'paypal') {
                $this->set('activepanel','paypal');
                $this->getPayPalForm(
                    $formData->membership_type, $formData->invoicenumber, $memberName
                );
            }
            else {
                $this->set('activepanel','checks');
            }
            $this->sendNotifications($formData);
            $this->set('totalCost','$'.$formData->cost);
            $this->set('membershipType',$formData->membership_type);
            $message =
                'Membership saved for '.
                $formData->member_first_name.' '.$formData->member_last_name.' ('.$formData->member_email.')<br>Thanks for Joining AFTM!';


            $this->clearFormData();


            $this->set('response', $message);
            return true;
        }
    }

    private function setDefaults() {

        $this->set('membertypes', $this->getHelper()->getMembershipTypes());
        $this->set('payoptions',
            array(
                "paypal" => 'PayPal / Credit Card',
                "check" => "Check or money order"
            ));

        $this->set('membershipType','');
        $this->set('totalCost','');


    }

    private function setCaptcha() {
        $setting = AftmConfiguration::getValue('captcha','form-member');
        $this->showCaptcha = (!empty($setting));
        $this->set('showCaptcha',$this->showCaptcha);
    }

    /**
     * Controller 'view' action. Invoked before processing template ../aftm_member_form.php
     */
    public function view()
    {
        $this->clearFormData();
        $this->set('totalCost','');
        $this->setCaptcha();
        $this->setDefaults();
        $this->set('activepanel','memberform');
    }
}
