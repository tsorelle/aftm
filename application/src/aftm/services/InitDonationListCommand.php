<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/31/2017
 * Time: 7:59 AM
 */

namespace Application\Aftm\services;


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
        $result->yesrlist = AftmDonationManager::GetDonationYearList();
        $result->donations = AftmDonationManager::GetDonationList($result->year);
        $this->setReturnValue($result);
    }
}