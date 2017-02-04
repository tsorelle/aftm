<?php
namespace Application\Block\ExternalForm\Form\Controller;

use Application\Aftm\AftmConfiguration;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMemberEntityManager;
use Application\Aftm\PayPalForm;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Http\Request;
use Concrete\Core\Utility\Service\Text;
use Core;
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

    // private $showCaptcha = true; // set false for test sessions
    private $showCaptcha = false;
    private $membershipTypes =  array(
        "" => "--- Select ---",
        "Student 1-year" => "Student 1-year - $15.00",
        "Individual 1-year" => "Individual 1-year - $20.00",
        "Family 1-year" => "Family 1-year - $25.00",
        "Band or Dance Group 1-year" => "Band or Dance Group - 1-year $25.00",
        "Business 1-year" => "Business - 1-year $50.00",
        "Individual 5-year" => "Individual  - 5-year $80.00",
        "Family 5-year" => "Family - 5-year - $100.00",
        "Lifetime membership" => "Lifetime membership - $300.00"
    );

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
    private function getPayPalForm($memberType,$invoicenumber,$customValue) {
        $form = PayPalForm::CreateStoredForm('member');
        $ipnenabled = AftmConfiguration::getValue('ipnenabled','form-mail',false);
        if ($ipnenabled) {
            $form->setIpnListner();
        }

        $form->setSelectedItem('Membership Type',$memberType);
        $form->setInvoiceNumber($invoicenumber);
        $form->setCustomValue($customValue);
        $form->setReturnUrl();
        $results = $form->getMarkup(); // with 'autolaunch' immediate redirect to PayPal
        // $results = $form->getMarkup(false); // false = no autolaunch, show PayPal button, use for testing.
        $this->set('paypalform',$results);
    }

    private function getInvoiceAddress($formData) {
        $result = '';
        if (!empty($formData->member_address1)) {
            $result = $formData->member_address1;
        }
        if (!empty($formData->amember_ddress2)) {
            $result .= (empty($result) ? '' : ',').$formData->member_address2;
        }
        $city = trim($formData->member_city.' '.$formData->member_state.' '.$formData->member_zipcode);
        if (!empty($city)) {
            $result .= (empty($result) ? '' : ',') . $city;
        }
        return $result;
    }

    private function postInvoice($formData) {
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
    /**
     * Send email notifications before paypal submission
     *
     * @param $formData  - from getRequestValues()
     */
    private function sendNotifications($formData) {
        // todo: implement sendNotifications
        $enabled = AftmConfiguration::getValue('email','form-member');
        if (!$enabled) {
            return;
        }
        $recipients = AftmConfiguration::getEmailValues('notifications1','form-member');
        if (empty($recipients)) {
            return;
        }

        /**
         * @var \Concrete\Core\Mail\Service
         * see https://documentation.concrete5.org/developers/sending-mail/working-mail-service
         */
        $mailService = Core::make('mail');
        $testing = AftmConfiguration::getValue('emailtesting','site');
        $mailService->setTesting($testing); // or true to throw an exception on error
        $mailService->addParameter('name','my name is mud');// $formData->member_first_name.' '.$formData->member_last_name);
        $mailService->load('form_member_notification1','aftm');
        $mailService->setSubject('Membership recieved');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        foreach ($recipients as $recipient) {
            $mailService->to($recipient);
        }
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
            $this->setDefaults();
            $this->setCaptcha();
            $formData = $this->getRequestValues();
            if (!$this->validate($formData)) {
                $this->set('formData',$formData);
                $this->set('activepanel','memberform');
                return false;
            }

            $formData->cost = $this->getCost($formData->membership_type);
            $formData->invoicenumber = $this->postInvoice($formData);
            AftmMemberEntityManager::AddMembership($formData);

            if ($formData->payment_method == 'paypal') {
                $this->set('activepanel','paypal');
                $this->getPayPalForm(
                    $formData->membership_type, $formData->invoicenumber, $formData->memberId
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

    private function getCost($membershipType) {
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

    private function setDefaults() {

        // Important! values must match those defined in the PayPal hosted form.
        // See \application\src\aftm\config.ini [form-member] for hosted button id numbers.
        $this->set('membertypes', $this->membershipTypes);
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
