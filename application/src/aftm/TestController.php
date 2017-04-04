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
use Application\Aftm\forms\DonationFormHelper;
use Application\Aftm\forms\MembershipFormHelper;
use Application\Tops\sys\IUser;
use Application\Tops\sys\IUserFactory;
use Application\Tops\sys\TObjectContainer;
use Application\Tops\sys\TUser;
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

        echo "<pre>\n";
        echo "Starting test\n";

        try {
            // call test functions

            // $this->donationInitServiceTest();
            // $this->membershipFormTests();
            // $this->donationServicesTest();
            $this->membershipServicesTest();
        }
        catch (\Exception $ex) {
            echo "\nFailed ".$ex->getFile()."(".$ex->getLine().") \n";
            echo $ex->getMessage();
            echo "\n";
            echo $ex->getTraceAsString();
            echo "\n";
        }

        echo "\nTest Done\n</pre>";
    }

    public function membershipServiceTests() {

    }

    public function membershipFormTests() {
        echo "\nStarted membership form tests\n";
        $testval = mktime().' Testy Street';
        $formData = new \stdClass();
        $formData->new_or_renewal            = 'new';
        $formData->payment_method            = 'paypal';
        $formData->member_first_name         = 'Tom';
        $formData->member_last_name          = 'Tester';
        $formData->member_address1           = $testval;
        $formData->member_address2           = 'address 2';
        $formData->member_city               = 'city';
        $formData->member_state              = 'state';
        $formData->member_zipcode            = '12345';
        $formData->member_email              = 'e@mail.com';
        $formData->membership_type           = 'Individual 5-year';
        $formData->member_band_name          = 'foos';
        $formData->member_band_website       = 'foo.bar.com';
        $formData->member_ideas              = 'some ideas';
        $formData->volunteer = new \stdClass();
        $formData->volunteer->concerts	 = false;
        $formData->volunteer->newsletter = false;
        $formData->volunteer->publicity  = false;
        $formData->volunteer->festivals  = false;
        $formData->volunteer->membership = true;
        $formData->volunteer->mailings   = true;
        $formData->volunteer->webpage    = true;

        $helper = new MembershipFormHelper();
        $helper->AddMembership($formData);
        $membership = AftmMembershipManager::UpdatePayment($formData->invoicenumber,$formData->cost,'Paypal speaks');
        echo "\nTest result: ".($membership->address1 == $testval ? 'Success' : 'Failed')."\n";
        echo "\nCompleted membership form tests\n";
    }

    public function donationInitServiceTest() {
        echo "\nDonation init started.\n";
        $service = new services\donations\InitDonationListCommand();
        $response = $service->runTest();
        $this->showServiceResponse($response);
        echo "\nDonation init complete.\n";
    }

    public function membershipServicesTest() {
        $testval = mktime()." street address";
        $membership = new \stdClass();
        $membership->firstname = 'Still Another New';
        $membership->lastname = 'Membership';
        $membership->address1 = $testval;
        $membership->address2 = 'address2';
        $membership->city = 'city';
        $membership->state = 'state';
        $membership->postalcode = '19209';
        $membership->email = 'e@mail.com';
        $membership->startdate = '2017-04-1';
        $membership->notes = 'notes';
        $membership->membershiptype      = 'Individual 5-year';
        $membership->groupname           = '';
        $membership->groupwebsite        = '';
        $membership->volunteerinterests  = 'membership, mailings, webpage';
        // $membership->reneweddate         = '';
        // $membership->expirationdate      = '';
        $membership->paymentmethod       = 'paypal';
        // $membership->paymentreceiveddate = '';
        // $membership->invoicenumber       = '';
        $membership->neworrenewal        = 'new';
        // $membership->amount              = '';
        $membership->ideas               = 'ideas';
        $membership->paypalmemo          = 'hello from paypal';

        $request = new \stdClass();
        $request->membership = $membership;
        $request->year = '2013';

        $service = new services\membership\UpdateMembershipCommand();
        $service->setRequest($membership);
        $result = $service->runTest($request);
        $this->showServiceResponse($result);
        echo "\nMembership insert complete.\n";

        echo "\nStart get test\n";
        $request = new \stdClass();
        $request->membershipId = $this->getIdValue("select id from aftmmemberships where address1 =  '$testval'" );
        $service = new services\membership\GetMembershipCommand();
        $response = $service->runTest($request);
        $this->showServiceResponse($response);
        $membership = $response->Value;

        echo "\nCompleted Get test\n";

        echo "\nStart update test\n";
        $testval = 'Test 2: '.mktime();
        $request = new \stdClass();
        $membership->lastname = $testval;
        $membership->address2 = 'updated';
        $request->membership = $membership;

        $service = new services\membership\UpdateMembershipCommand();
        $service->setRequest($membership);
        $response = $service->runTest($request);
        $this->showServiceResponse($response);
        $message = 'not returned';
        foreach ($response->Value->memberships as $item) {
            if ($item->lastname == $testval) {
                $message = "Found";
                break;
            }
        }
        echo "\n$message\n";

        echo "\nStart membership delete test\n";
        $service = new services\membership\DeleteMembershipCommand();
        $request = new \stdClass();
        $request->membershipId = $membership->id;
        $request->year = 2017;
        $response = $service->runTest($request);
        $this->showServiceResponse($response);

    }

    public function donationServicesTest()
    {
        $testval = mktime()." street address";
        $donation = new \stdClass();
        $donation->firstname = 'Still Another New';
        $donation->lastname = 'Donation 3';
        $donation->address1 = $testval;
        $donation->address2 = 'address2';
        $donation->city = 'city';
        $donation->state = 'state';
        $donation->postalcode = '19209';
        $donation->email = 'e@mail.com';
        $donation->phone = '122-029-0290';
        $donation->amount = '30.00';
        $donation->datereceived = '2017-04-1';
        $donation->notes = 'notes';

        $request = new \stdClass();
        $request->donation = $donation;
        $request->year = '2013';

        $service = new services\donations\UpdateDonationCommand();
        $service->setRequest($donation);
        $result = $service->runTest($request);
        $this->showServiceResponse($result);
        echo "\nDonation insert complete.\n";

        echo "\nStart get test\n";
        $request = new \stdClass();
        $request->donationId = $this->getIdValue("select id from aftmdonations where address1 =  '$testval'" );
        $service = new services\donations\GetDonationCommand();
        $response = $service->runTest($request);
        $this->showServiceResponse($response);
        $donation = $response->Value;

        echo "\nCompleted Get test\n";

        echo "\nStart update test\n";
        $testval = 'Test 2: '.mktime();
        $request = new \stdClass();
        $donation->lastname = $testval;
        $donation->address2 = 'updated';
        $request->donation = $donation;

        $service = new services\donations\UpdateDonationCommand();
        $service->setRequest($donation);
        $response = $service->runTest($request);
        $this->showServiceResponse($response);
        $message = 'not returned';
        foreach ($response->Value as $item) {
            if ($item->lastname == $testval) {
                $message = "Found";
                break;
            }
        }
        echo "\n$message\n";

        echo "\nStart donation delete test\n";
        $service = new services\donations\DeleteDonationCommand();
        $request = new \stdClass();
        $request->donationId = $donation->id;
        $request->year = 2017;
        $response = $service->runTest($request);
        $this->showServiceResponse($response);
    }

    public function getDonationsYearsTest()
    {
        $years = AftmDonationManager::GetDonationYearList();
        print_r($years);
    }

    public function updateDonationTest() {

        // $donations = AftmDonationManager::GetDonationList(2016);
        // $all = AftmDonationManager::GetDonationList();

        $donation = AftmDonationManager::GetDonation(1);
        $donation->id = 0;
        $donation->firstname = 'New';
        $donation->lastname = 'Boy';
        // $donation->donationnumber = '9999999';
        $donation->amount = 100.00;
        // $donation->datereceived = date("Y-m-d");
        $donation->notes = 'updated by test 2';
    }

    public function doTestAuth()
    {
        $user = TUser::GetCurrent();
        if ($user->isAuthenticated()) {
            echo '<br>'. ($user->isAdmin() ? 'Administrator' : 'Authenticated user').'<br>';
            echo "<br>Email: " . $user->getEmail() . "<br>";
        }
        else {
            echo "<br>Not authenticated<br>";
        }
        echo "<br>This user ".
         ($user->isAuthorized('donations.edit') ? "CAN" : "CANNOT").
            " edit donations<br>";
        echo "<br>Done<br>";
    }

    public function factoryTest()
    {
        /**
         * @var $factory IUserFactory
         */
        $factory = TObjectContainer::Get('tops.userfactory');

        /**
         * @var $user IUser
         */
        $user = $factory->createUser();

        echo "<br>". $user->getEmail()."<br>";

        echo "<br>Done<br>";
    }

    public function donationFormTests() {

        echo "Donation form add test started\n";

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

        $helper = new DonationFormHelper();
        $helper->AddDonation($donationData);
        $result = AftmDonationManager::UpdatePayment($donationData->donation_invoice_number,256.90,'Paypal says ok!');
        echo "Donation form add test done.\n";
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

    private function getIdValue($sql)
    {
        $db = \Database::connection();
        $stmt = $db->query($sql);
        $result = $stmt->fetch();
        return empty($result) ? false : $result['id'];
    }

    private function showServiceResponse($response,$showValue = false) {
        echo "\n";
        switch ($response->Result) {
            case 0 : echo "Service succeeded\n"; break;
            case 2 : echo "Service succeeded with warnings\n"; break;
            case 3 : echo "Service errors occured\n"; break;
            case 4 : echo "Service failed"; break;
            case 5 : echo "Service not available"; break;
        }
        if (!empty($response->Messages)) {
            foreach ($response->Messages as $message) {
                switch($message->MessageType ) {
                    case 0: echo "Info: "; break;
                    case 1: echo "Error: "; break;
                    case 2 : echo "Warning: "; break;
                }
                echo "$message->Text\n";
            }
        }
        if ($showValue) {
            var_dump($response->Value);
            echo("\n");
        }
        else {
            echo empty($response->Value) ? "No return value\n" : "A value was returned.\n";
        }
    }



}