<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/30/2017
 * Time: 5:36 PM
 */

namespace Application\Aftm\services;


use Application\Aftm\AftmDonationManager;
use Application\Tops\services\TServiceCommand;

class GetDonationListCommand extends TServiceCommand
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
        $year = (isset($request->year) && is_numeric($request->year)) ? $request->year : null;
        $result = AftmDonationManager::GetDonationList($year);
        $this->setReturnValue($result);
    }
}