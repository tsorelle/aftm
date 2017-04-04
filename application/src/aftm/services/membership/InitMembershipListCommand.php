<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 3:38 PM
 */

namespace Application\Aftm\services\membership;


use Application\Aftm\AftmMembershipManager;
use Application\Tops\services\TServiceCommand;

class InitMembershipListCommand extends TServiceCommand
{
    public function __construct()
    {
        $this->addAuthorization('memberships.view');
    }

    protected function run()
    {
        $result = new \stdClass();
        $result->year = date("Y");
        $result->canEdit = $this->getUser()->isAuthorized('memberships.edit');
        $result->yesrlist = AftmMembershipManager::GetMembershipYearList();
        $result->memberships = AftmMembershipManager::GetMembershipList($result->year);
        $this->setReturnValue($result);
    }
}