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

        $test = new \stdClass();
        $test->bar = 1;
        echo "foo ";
        echo empty($test->foo) ? "empty<br>" : "not empty<br>";
        echo "bar ";
        echo empty($test->bar) ? "empty<br>" : "not empty<br>";

        /*
        $mailService = Core::make('mail');
        $mailService->addParameter('mailContent','<h2>Hello Person</h2><p>Welcome to AFTM.</p>');
        $mailService->addParameter('logo',AftmMailManager::GetLogo());
        $mailService->load('aftm/testmail');
        $mailService->setSubject('Welcome to AFTM');
        $mailService->from('atfmtexas@gmail.com', 'Austin Friends of Traditional Music');
        $mailService->to('tls@2quakers.net','Terry SoRelle');
        $mailService->sendMail();


                $result = AftmCatalogManager::GetPrice('membership','Family 1-year');
                echo '<br>'.($result == '25.00' ? 'success!' : 'failed').'<br>';

                // $invoice = AftmInvoiceManager::Get('10000002');
                // AftmInvoiceManager::Update('00000002');


               $manager = new AftmMemberEntityManager();
                // $manager->createMemberEntity();
                $data=array(
                    'member_first_name' => 'Liz',
                    'member_last_name' => 'Yeats',
                    'member_address1' => '904 E. Meadowmere',
                    'member_address2' => '',
                    'member_city' => 'Austin',
                    'member_state' => 'TX',
                    'member_zipcode' => '78758',
                    'member_email' => 'tls@2quakers.net',
                    'membership_type' => 'Individual 5-year',
                    'member_band_name' => '',
                    'member_band_website' => '',
                    'member_volunteer_interest' => 'any',
                    'member_payment_method' => 'paypal',
                    'member_invoice_number' => '00000212',
                    'new_or_renewal' => 'new',
                    'member_ideas' => 'some ideas'
                );
                $manager->insertMemberEntry($data);
                */

        echo "<br>Done<br>";

    }


}