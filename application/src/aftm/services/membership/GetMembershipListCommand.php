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

class GetMembershipListCommand extends TServiceCommand
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
        $year = (isset($request->year) && is_numeric($request->year)) ? $request->year : null;
        $result = AftmMembershipManager::GetMembershipList($year);
        $this->setReturnValue($result);
    }
}