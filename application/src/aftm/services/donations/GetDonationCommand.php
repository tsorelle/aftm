<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 5:58 PM
 */

namespace Application\Aftm\services\donations;


use Application\Aftm\AftmDonationManager;
use Application\Tops\services\TServiceCommand;

class GetDonationCommand extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('donations.view');
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
        $result = AftmDonationManager::GetDonation($request->donationId);
        if (empty($result)) {
            $this->addErrorMessage("No donation found for id# $request->donationId");
            return;
        }
        $this->setReturnValue($result);
    }
}