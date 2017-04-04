<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/31/2017
 * Time: 7:59 AM
 */

namespace Application\Aftm\services\donations;


use Application\Aftm\AftmDonationManager;
use Application\Tops\services\TServiceCommand;

class InitDonationListCommand extends TServiceCommand
{
    public function __construct()
    {
        $this->addAuthorization('donations.view');
    }

    protected function run()
    {
        $result = new \stdClass();
        $result->year = date("Y");
        $result->yearlist = AftmDonationManager::GetDonationYearList();
        $result->donations = AftmDonationManager::GetDonationList($result->year);
        $result->canEdit = $this->getUser()->isAuthorized('donations.edit');
        $this->setReturnValue($result);
    }
}