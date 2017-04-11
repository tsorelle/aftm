<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 3:38 PM
 */

namespace Application\Aftm\services\membership;


use Application\Aftm\AftmCatalogManager;
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
        $result = AftmMembershipManager::GetMembershipListAndYears(date("Y"));
        $types = AftmCatalogManager::GetObjectList('membership');
        $result->membershiptypes = array_values($types);
        $result->canEdit = $this->getUser()->isAuthorized('memberships.edit');
        $this->setReturnValue($result);
    }
}