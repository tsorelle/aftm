<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/4/2017
 * Time: 7:10 AM
 */

namespace Application\Aftm\services\donations;


use Application\Aftm\AftmDonationManager;
use Application\Aftm\AftmInvoiceManager;
use Application\Tops\services\TServiceCommand;

class DeleteDonationCommand extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('donations.edit');
    }

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        if (!isset($request->donationId)) {
            $this->addErrorMessage('No donation id received.');
            return;
        }
        $year = (isset($request->year) && is_numeric($request->year)) ? $request->year : null;

        $donation = AftmDonationManager::GetDonation($request->donationId);
        if (!empty($donation)) {
            if (!empty($donation->donationnumber)) {
                AftmInvoiceManager::RemoveInvoice($donation->donationnumber);
            }
            AftmDonationManager::RemoveDonation($request->donationId);
        }


        $result = AftmDonationManager::GetDonationListAndYears($year);

        $this->setReturnValue($result);

        $this->addInfoMessage('Donation removed.');

    }
}