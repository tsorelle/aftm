<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/11/2017
 * Time: 6:00 AM
 */

namespace Application\Block\ExternalForm\Form\Controller;

use Application\Aftm\AftmMailManager;
use Core;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Http\Request;
use Application\Aftm\AftmConfiguration;
use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMemberEntityManager;
use Application\Aftm\PayPalForm;
use Application\Aftm\AftmCatalogManager;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Express;



class AftmDonationForm extends AbstractController
{
    private $showCaptcha = false;


    private function setCaptcha()
    {
        $setting = AftmConfiguration::getValue('captcha', 'form-donation');
        $this->showCaptcha = (!empty($setting));
        $this->set('showCaptcha', $this->showCaptcha);
    }

    /**
     * Blank all data fields in form or return to defaults
     */
    private function clearFormData() {
        $formData = new \stdClass();
        // $formData->payment_method              = 'paypal';
        $formData->donation_amount           = '';
        $formData->donor_first_name           = '';
        $formData->donor_last_name            = '';
        $formData->donor_address1             = '';
        $formData->donor_address2             = '';
        $formData->donor_city                 = '';
        $formData->donor_state                = '';
        $formData->donor_zipcode              = '';
        $formData->donor_email                = '';
        $formData->donor_phone                = '';

        $this->set('formData',$formData);
    }
    private function setDefaults() {

        // todo: not needed?

    }
    
    /**
     * Controller 'view' action. Invoked before processing template ../aftm_member_form.php
     */
    public function view()
    {
        $this->clearFormData();
        $this->set('totalCost', '');
        $this->setCaptcha();
        $this->setDefaults();
        $this->set('activepanel', 'donationform');
    }

    private function getRequestValues() {
        $formData = new \stdClass();
        $request = Request::getInstance();

        $textHelper = Core::make('helper/text');

        $formData->donation_amount            = $textHelper->sanitize($request->get('donation_amount'));
        // $formData->payment_method            = $textHelper->sanitize($request->get('payment_method'));
        $formData->donor_first_name         = $textHelper->sanitize($request->get('donor_first_name'));
        $formData->donor_last_name          = $textHelper->sanitize($request->get('donor_last_name'));
        $formData->donor_address1           = $textHelper->sanitize($request->get('donor_address1'));
        $formData->donor_address2           = $textHelper->sanitize($request->get('donor_address2'));
        $formData->donor_city               = $textHelper->sanitize($request->get('donor_city'));
        $formData->donor_state              = $textHelper->sanitize($request->get('donor_state'));
        $formData->donor_zipcode            = $textHelper->sanitize($request->get('donor_zipcode'));
        $formData->donor_email              = $textHelper->sanitize($request->get('donor_email'));
        $formData->donor_phone              = $textHelper->sanitize($request->get('donor_phone'));

        return $formData;
    }
    
    private function getInvoiceAddress($formData) {
        $result = '';
        if (!empty($formData->donor_address1)) {
            $result = $formData->donor_address1;
        }
        if (!empty($formData->adonor_ddress2)) {
            $result .= (empty($result) ? '' : ',').$formData->donor_address2;
        }
        $city = trim($formData->donor_city.' '.$formData->donor_state.' '.$formData->donor_zipcode);
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
        if (empty($formData->donation_amount)) {
            $this->set('errormessage','You must enter the amount of your donation.');
            return false;
        }
        // todo: validate number
        
        if (empty($formData->donor_first_name) || empty($formData->donor_last_name)) {
            $this->set('errormessage','You must enter first and last name');
            return false;
        }
        if (empty($formData->donor_address1) ||
            empty($formData->donor_city) ||
            empty($formData->donor_state) ||
            empty($formData->donor_zipcode)) {
            $this->set('errormessage','Please enter full address including address, city, state/province, postal code or country');
            return false;
        }
        if (!filter_var($formData->donor_email, FILTER_VALIDATE_EMAIL)) {
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
    public function action_submit_donation($bID = false)
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

            // $formData->cost = $this->getCost($formData->membership_type);
            $formData->invoicenumber = $this->postInvoice($formData);
            // todo: finish action_submit_donation
            /*
            AftmDonationEntityManager::AddDonation($formData);

            $donorName = $formData->donor_first_name.' '.$formData->donor_last_name;
            $formData->donorId = $donorName;
            if ($formData->payment_method == 'paypal') {
                $this->set('activepanel','paypal');
                $this->getPayPalForm(
                    $formData->donorship_type, $formData->invoicenumber, $donorName
                );
            }
            else {
                $this->set('activepanel','checks');
            }
            $this->sendNotifications($formData);
            $this->set('totalCost','$'.$formData->cost);
            $this->set('donorshipType',$formData->donorship_type);
            $message =
                'Membership saved for '.
                $formData->member_first_name.' '.$formData->member_last_name.' ('.$formData->member_email.')<br>Thanks for Joining AFTM!';

            */
            $this->clearFormData();


            // $this->set('response', $message);
            return true;
        }

    }

}