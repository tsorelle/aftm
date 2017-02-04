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



abstract class IpnControllerBase extends Controller
{
    private $invoicenumber = 'unknown';
    private $ipndebug = false;  // log messages except verbose
    const debugVerbose = 'verbose'; // log verbose messages
    const debugPhpLog = 'phplog'; // log to php error log
    const debugLocal = 'local'; // write messages to html response
    const debugDump = 'dump';  // log post values to database

    private function writeLog($message,$debugmode = false) {
        if ($this->ipndebug == self::debugLocal) {
            echo "<p>$message</p>";
            return;
        }

        if ($debugmode === self::debugVerbose && $this->ipndebug !== self::debugVerbose ) {
            return;
        }

        $formId = $this->getFormId();

        if ($this->ipndebug == self::debugPhpLog) {
            error_log("ipnlistener ('$formId'): ".$message,0);
            return;
        }

        try {
            $db = \Database::connection();
            $db->insert('aftmipnlog', array(
                'formname' => $this->getFormId(),
                'invoicenumber' => $this->invoice,
                'message' => $message
            ));
        } catch (Exception $e) {
            error_log('ipnlistener: database logging failed: '.$e->getMessage() ,0);
            if ($this->ipndebug != self::debugPhpLog) {
                error_log("ipnlistener ('$formId'): " . $message, 0);
            }
        }
        // write to database log here



        /*
        if (!isset($this->log)) {
            $logpath = __DIR__ . '/logs/ipnlistener.txt';
            $log = fopen($logpath);
            if (empty($this->log)) {
                error_log("ipnlistener: Cannot open log '" . $logpath . "'.",0);
                $this->nolog = true;
            }
            if ($this->nolog) {
                error_log("ipnlistener: ".$message,0);
            }
            else {
                fwrite($this->log, "$message\n");
                fclose($this->log);
            }
        }
        */
    }

    /**
     * Read the post from PayPal system and add 'cmd'
     *
     * @return string
     */
    private function buildVerificationRequest() {
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        $req = 'cmd=_notify-validate';

        foreach ($myPost as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }
        return $req;
    }

    private function verifyPayPalRequest($sandboxMode = 1) {

        $verificationRequest= $this->buildVerificationRequest();

        if ($sandboxMode == 1) {
            $curl_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $curl_url = 'https://www.paypal.com/cgi-bin/webscr';
        }

        if ($this->ipndebug == self::debugLocal) {
            return true;
        }

        $ch = \curl_init($curl_url);
        \curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $verificationRequest);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        \curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: XXXXXXXXXXXXXXXX', 'Connection: Close'));

        // In wamp like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below.
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if( !($res = curl_exec($ch)) ) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            \curl_close($ch);
            exit;
        }
        \curl_close($ch);
        return (strcmp ($res, "VERIFIED") == 0);
    }
    

    private function logDbInfo()
    {
        /*
        if (defined('DB_USERNAME')) {
            // logging for debug - terry sorelle aftm.us
            error_log("ipn_listener ".DB_USERNAME, 0);
        }
        else {
            // logging for debug - terry sorelle aftm.us
            error_log("ipn_listener DB_USERNAME not defined.", 0);

        }
        if (defined('DB_DATABASE')) {
            // logging for debug - terry sorelle aftm.us
            error_log("ipn_listener ".DB_DATABASE, 0);
        }
        if (defined('DB_SERVER')) {
            // logging for debug - terry sorelle aftm.us
            error_log("ipn_listener ".DB_SERVER, 0);
        }
*/

    }

    private function checkForFraud($inputs) {

    }

    protected function getPostValue($key)
    {
        $value = isset($_POST[$key]) ? $_POST[$key] : '';
        return $value;
    }

    private function dumpPostVars() {

    }

    /**
     * @return \stdClass
     *
     * Example:
     * $result = new \stdClass();
     * $result->item_name = $this->getPostValue('item_name');
     * return $result
     */
    abstract function getPostValues();

    /**
     * @param \stdClass $params
     *      with elements request, details, invoice
     * @return mixed
     */
    abstract function sendNotifications($params);

    /**
     * @param \stdClass $params
     *      with elements request, details, invoice
     * @return boolean
     */
    abstract function updateData($params);

    /**
     * Get transaction details from data
     *
     * @param array $inputs
     * @return \stdClass
     */
    abstract function getDetails($invoicenumber);

    /**
     * @return string
     */
    abstract function getFormId();

    /**
     *
     */
    public function handleResponse() {
        $configSection = 'form-'.$this->getFormId();
        // $this->ipndebug = (!empty(AftmConfiguration::getValue('ipndebug',$configSection,false)));
        $this->ipndebug = AftmConfiguration::getValue('ipndebug',$configSection,false);
        $request = Request::getInstance();
        $sandboxMode = $request->get('sandbox');
        $formId = $this->getFormId();

        // logging for debug - terry sorelle aftm.us
        $this->writeLog("IPN Listener for $formId recieved message");
        $this->writeLog("Sandbox: ". ($sandboxMode? 'yes':'no'), self::debugVerbose);

        $verify = AftmConfiguration::getValue('ipnverify',$configSection,false);
        if (!empty($verify)) {
            if (!$this->verifyPayPalRequest($sandboxMode)) {
                $this->writeLog('IPN ERROR: Cannot verify paypal request');
                return;
            }
        }

        $this->writeLog("IPN listener request VERIFIED",self::debugVerbose);

        if ($this->ipndebug == self::debugDump) {
            foreach ($_POST as $key => $value ) {
                $this->writeLog("post: $key = $value");
            }
        }
        $inputs = $this->getPostValues();

        $this->checkForFraud($inputs);

        if (empty($inputs->invoice)) {
            $this->writeLog('IPN ERROR: No invoice number in IPN request.');
            return;
        }
        $this->invoice = $inputs->invoice;

        $params = new \stdClass();
        $params->request = $inputs;

        if ($this->ipndebug == self::debugDump) {
            $this->writeLog("IPN listener test coupleted.  Form:'$formId', Invoice number: '$inputs->invoice'");
            return;
        }

        $count = AftmInvoiceManager::Update($inputs->invoice);
        if ($count > 0) {
            $params->invoice = AftmInvoiceManager::Get($inputs->invoice);
            $params->details = $this->getDetails($inputs->invoice);
        }
        else {
            $params->invoice = false;
            $params->details = false;
            $this->writeLog("IPN warning: Invoice #$inputs->invoice not found.");
        }

        $this->sendNotifications($params);
        $this->updateData($params);

        $this->writeLog("IPN listener coupleted.  Form:'$formId', Invoice number: '$inputs->invoice'");
    }
}