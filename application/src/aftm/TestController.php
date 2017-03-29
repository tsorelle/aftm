<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:54 AM
 */

namespace Application\Aftm;

use Application\Aftm\AftmConfiguration;
use Application\Aftm\concrete\AftmFileManager;
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

        $donationData = new \stdClass();

        $donationData->donor_first_name   = 'Terry';
        $donationData->donor_last_name    = 'SoRelle';
        $donationData->donation_invoice_number = '00009999';
        $donationData->donor_address1     = 'Address 1';
        $donationData->donor_address2     = 'Address 2';
        $donationData->donor_city        = 'Austin';
        $donationData->donor_state        = 'TX';
        $donationData->donor_zipcode      = '78767';
        $donationData->donor_email       = 'e@mail.com';
        $donationData->donor_phone       = '512-909-0292';

        AftmDonationManager::AddDonation($donationData);

        $result = AftmDonationManager::UpdatePayment($donationData->donation_invoice_number,256.90,'Paypal says ok!');


        echo "<br>Done<br>";
    }


    public function fileSetTest() {
        $setName = 'Newsletters';

        $result = AftmFileManager::GetFilesBySet($setName);
        if ($result === false) {
            exit('No set');
        }

        foreach ($result as $f) {
            echo "<p>$f->title</p>";
        }


        echo "<br>Done<br>";
    }


}