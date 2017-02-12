<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:54 AM
 */

namespace Application\Aftm;

use Application\Aftm\AftmConfiguration;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Http\Request;
use Core;
use Concrete\Core\Utility\Service\Text;
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Support\Facade\Express;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Express\Entry\Search\Result\Result;
use Concrete\Core\Express\EntryList;


class TestController extends Controller
{


    public function doTest() {
        $formData = new \stdClass();
        $formData->donor_first_name           = 'Terry';
        $formData->donor_last_name            = 'SoRelle';
        $formData->donor_address1             = '904 E. Meadowmere';
        $formData->donor_address2             = '';
        $formData->donor_city                 = 'Austin';
        $formData->donor_state                = 'TX';
        $formData->donor_zipcode              = '78758';
        $formData->donor_email                = 'tls@2quakers.net';
        $formData->donor_phone                = '512-789-7321';
        $formData->donation_invoice_number   = '0000000028';

        $invoice = '0000000028';
        AftmDonationEntityManager::UpdatePayment($invoice,'10.38');

        echo "<br>Done<br>";

    }


}