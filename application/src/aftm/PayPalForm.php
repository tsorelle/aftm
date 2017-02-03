<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:15 AM
 */

namespace Application\Aftm;


use Exception;

/**
 * Class PayPalForm
 * @package Application\Aftm
 *
 * Used by form controller methods, see application/blocks/external_form/form/controller/aftm_member_form.php
 *
 * Creates PayPal button markup
 */
class PayPalForm
{
    /**
     * @var  string
     *
     * identifier for configuration and ipn post back address. e.g. 'member'
     */
    private $formId;

    /**
     * Content of form body
     * @var array
     */
    private $formLines;
    /**
     * @var bool
     */
    private $sandboxMode;
    /**
     * URL for PayPal IPN callback.  See setIpnListner()
     *
     * @var string
     */
    private $ipnUrl;
    /**
     * @var string
     */
    private $currencyCode = 'USD';


    function __construct($formId='')
    {
        $this->formId = $formId;
        $this->formLines = array();
    }

    public function setCurrency($code) {
        $this->currencyCode = $code;
    }

    /**
     * Add a hidden field tag to form body
     * @param $name
     * @param $value
     */
    public function addHiddenField($name,$value) {
        $this->formLines[] = '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    }

    /**
     * Add hidded field for accountid.
     * value from config.ini [paypal]
     * @throws Exception
     */
    public function addAccountId() {
        $accountId = AftmConfiguration::getValue('paypal','accountid');
        if (empty($accountId)) {
            throw new Exception('Account id not found in AFTM configuration file.');
        }
        $this->addHiddenField("business",$accountId);
    }

    public function setSandboxMode($value = 1) {
        $this->sandboxMode = $value;
    }

    /**
     * Build form markup. Call this only after all hidden fields are added and non-default values are set.
     *
     * @param bool $autolaunch - true: immediate redirect to Paypal; false: Show PayPal button
     * @return string - html markup for form
     */
    public function getMarkup($autolaunch = true) {
        $action = ($this->sandboxMode == 1) ?
            'https://www.sandbox.paypal.com/cgi-bin/webscr':
            'https://www.paypal.com/cgi-bin/webscr';

        $this->addHiddenField("currency_code",$this->currencyCode);

        if (!empty($this->ipnUrl)) {
            $this->addHiddenField("notify_url",$this->ipnUrl);
        }


        $imgsrc = AftmConfiguration::getValue('site','imgsrc','/packages/aftm/images');

        $formHeader = "<FORM id=\"paypalform\" action=\"$action\" method=\"post\">\n";

        if ($autolaunch) {
            $this->formLines[] = "<noscript>";
        }

        // $this->formLines[] = '<input type="submit" name="submit" value="Continue to PayPal" />';
        $this->formLines[] = '<h3>Continue to PayPal</h3>';
        $this->formLines[] = '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">';
        $this->formLines[] = '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';

        if ($autolaunch) {
            $this->formLines[] = "</noscript>";
        }
        $this->formLines[] = '</form>';

        if ($autolaunch) {
            $this->formLines[] = "<div id='paypal-launcher'>";
            $this->formLines[] = "<img src='$imgsrc/ajax-loader.gif' onload='document.getElementById(\"paypalform\").submit();' />";
            $this->formLines[] = '<span style="font-size: 18px;">Redirecting to PayPal. Please wait...</span>';
            $this->formLines[] = '</div>';
        }

        return $formHeader.implode("\n",$this->formLines);

    }

    /**
     * Build return path hidden field
     *
     * @param string $pagePath - relative path to landing page.
     */
    public function setReturnUrl($pagePath = '') {
        $url =  "http://".$_SERVER["SERVER_NAME"];
        if ($_SERVER["SERVER_PORT"] != "80") {
            $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        }
        if (!empty($pagePath)) {
            $url .= '/'.$pagePath;
        }

        $this->addHiddenField('return',$url);

    }

    /**
     * Build URL for IPN listener -  PayPal callback script
     * IPN prefix and protocol are defined in config.ini [paypal] section
     * See also: aftm/doc/ipnlistener.txt
     *
     * @param string $listener
     *
     * @see https://developer.paypal.com/webapps/developer/docs/classic/products/instant-payment-notification/
     */
    public function setIpnListner($listener='') {
        if (empty($listener)) {
            $listener = $this->formId.'/ipn';
        }
        $ipnProtocol = AftmConfiguration::getValue('paypal','ipnprotocol','http:');
        $ipnPrefix = AftmConfiguration::getValue('paypal','ipnprefix','aftm/paypal');
        $url =  $ipnProtocol."://".$_SERVER["SERVER_NAME"];
        if ($_SERVER["SERVER_PORT"] != "80") {
            $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        }
        $url .= '/'.$ipnPrefix.'.'.$listener."/".$this->sandboxMode;
        $this->ipnUrl = $url;
    }

    /**
     * Add hidden field for a custom value. Usually a select list item
     *
     * @param $name
     * @param $value
     */
    public function setSelectedItem($name,$value) {
        $this->addHiddenField('on0',$name);
        $this->addHiddenField('os0',$value);
    }

    /**
     * Add unique invoice id. Required for some hosted forms.
     *
     * @param $id
     */
    public function setInvoiceId($id) {
        $this->addHiddenField('invoice',$id);
    }

    /**
     * Add a custom value, e.g. a membership id
     *
     * @param $value
     */
    public function setCustomValue($value) {
        $this->addHiddenField('custom',$value);
    }

    /**
     * Initialize a PayPalForm instance.
     *
     * @param $itemName - as defined in hosted form
     * @param int $itemNumber - as defined in hosted form
     * @param string $cmdValue - PayPal cmd field.
     *
     * @return PayPalForm
     */
    public static function Create($itemName, $itemNumber=0, $cmdValue='_xclick')
    {
        $instance = new PayPalForm();
        $instance->addHiddenField("cmd",$cmdValue);
        $instance->addHiddenField("item_name",$itemName);
        $instance->addHiddenField("item_number",$itemNumber);
        return $instance;
    }

    /**
     * Initialize a PayPalForm instance for a hosted form (aka stored button)
     *  See https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/create_payment_button/
     *
     * @param $formid - identifier for form configuration in config.ini. E.g. member = [form-member] section.
     * @param string $cmdType - PayPal cmd value.  Will be perfixed with '_s-' for stored button.
     * @return PayPalForm
     * @throws Exception
     */
    public static function CreateStoredForm($formid, $cmdType='xclick')
    {
        $sectionKey = "form-$formid";
        $buttonId = '';
        $sandbox = AftmConfiguration::getValue('paypal','sandbox',0);
        if (empty($sandbox)) {
            $sandbox = AftmConfiguration::getValue('sandbox',$sectionKey, 0);
        }

        if ($sandbox) {
            $buttonId = AftmConfiguration::getValue('sandboxformid',$sectionKey);
        }

        if (empty($buttonId)) {
            $buttonId = AftmConfiguration::getValue('ppformid', $sectionKey);
            if (empty($buttonId)) {
                throw new Exception("No form id found in configuration for $sectionKey");
            }
        }

        $instance = new PayPalForm($formid);
        $instance->setSandboxMode($sandbox);
        $instance->addHiddenField("cmd","_s-".$cmdType);
        $instance->addHiddenField('hosted_button_id',$buttonId);
        return $instance;
    }
}