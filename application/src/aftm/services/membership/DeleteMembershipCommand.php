<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 4/4/2017
 * Time: 7:57 AM
 */

namespace Application\Aftm\services\membership;


use Application\Aftm\AftmInvoiceManager;
use Application\Aftm\AftmMembershipManager;
use Application\Tops\services\TServiceCommand;

class DeleteMembershipCommand extends TServiceCommand
{
    public function __construct()
    {
        $this->addAuthorization('memberships.edit');
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
        $membership = AftmMembershipManager::GetMembership($request->membershipId);
        if (!empty($membership->invoicenumber)) {
            AftmInvoiceManager::RemoveInvoice($membership->invoicenumber);
        }
        AftmMembershipManager::RemoveMembership($request->membershipId);
        $year = (isset($request->year) && is_numeric($request->year)) ? $request->year : null;
        $result = AftmMembershipManager::GetMembershipList($year);
        $this->setReturnValue($result);

        $this->addInfoMessage('Membership removed.');
    }
}