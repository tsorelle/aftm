<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 6:00 PM
 */

namespace Application\Aftm\services;


use Application\Aftm\AftmDonationManager;
use Application\Tops\services\TServiceCommand;

class UpdateDonationCommand extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('donation.edit');
    }

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        if (!isset($request->donation)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        $isnew = empty($request->donation);
        if ($isnew) {
            AftmDonationManager::NewDonation($request->donation);
        }
        else {
            AftmDonationManager::UpdateDonation($request->donation);
        }
        $year = (empty($request->year)) ? null : $request->year;
        if ($year != null && !is_numeric($year)) {
            $year = null;
        }
        $result = AftmDonationManager::GetDonationList($year);
        $this->setReturnValue($result);
    }
}