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
        $donation = AftmDonationManager::GetDonation($request->donationId);
        if (!empty($donation->donationnumber)) {
            AftmInvoiceManager::RemoveInvoice($donation->donationnumber);
        }
        AftmDonationManager::RemoveDonation($request->donationId);
        $year = (isset($request->year) && is_numeric($request->year)) ? $request->year : null;
        $result = AftmDonationManager::GetDonationList($year);
        $this->setReturnValue($result);

        $this->addInfoMessage('Donation removed.');
    }
}