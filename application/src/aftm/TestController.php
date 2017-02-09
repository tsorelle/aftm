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

    private function getContactInfo($mailManager, $formData) {
        $contactInfo = $mailManager->formatAddressHtml('904 E. Meadowmere','', 'Austin','TX','78758');
        if (!empty($formData->member_band_name)) {
            $contactInfo .= '<p>Group: '.$formData->member_band_name;
            if (!empty($formData->member_band_website)) {
                $contactInfo .= ' ('.$formData->member_band_website.')';
            }
            $contactInfo .= '</p>';
        }
        return $contactInfo;
    }

    public function doTest() {

        $formData = new \stdClass();
        $formData->member_band_name = 'Band of Gypsys';
        $formData->member_band_website = 'gypsys@foo.com';
        $formData->cost = '25';
        $formData->membership_type = 'Individual 5-year';
        $formData->payment_method = 'check';
        $formData->new_or_renewal = 'renewal';
        $formData->member_first_name = 'Terry';
        $formData->member_last_name = 'SoRelle';


        $mailManager = AftmMailManager::Create();
        $contactInfo = $this->getContactInfo($mailManager,$formData);
        $membershipType = $formData->membership_type. ($formData->new_or_renewal == 'new' ? '' : ' (renewal)');
        $checkInfo = $this->getCheckInfo($formData);
        $content = $mailManager->getTemplate('welcome_member.html',
            array('membername' => $formData->member_first_name.' '.$formData->member_last_name,
                'cost' => $formData->cost,
                'checkInfo' => $checkInfo,
                'contactInfo' => $contactInfo,
                'membership' => $membershipType));

        $contentHtml = str_replace('[[links]]',$mailManager->getLogoMarkup(),$content);
        $contentHtml = $mailManager->mergeHtml($contentHtml);
        $contentText = $mailManager->toPlainText($content);
        $contentText = str_replace('[[links]]',$mailManager->getPlainLinks(),$contentText);

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