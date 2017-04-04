<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/3/2017
 * Time: 3:36 PM
 */

namespace Application\Aftm\services\membership;


use Application\Aftm\AftmMembershipManager;
use Application\Tops\services\TServiceCommand;

class GetMembershipCommand extends TServiceCommand
{

    public function __construct()
    {
        $this->addAuthorization('memberships.view');
    }

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('No request received.');
            return;
        }
        if (!isset($request->membershipId)) {
            $this->addErrorMessage('No membership id received.');
            return;
        }
        $result = AftmMembershipManager::GetMembership($request->membershipId);
        if (empty($result)) {
            $this->addErrorMessage("No membership found for id# $request->membershipId");
            return;
        }
        $this->setReturnValue($result);
    }
}