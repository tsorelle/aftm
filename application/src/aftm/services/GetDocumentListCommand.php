<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/21/2017
 * Time: 5:43 AM
 */

namespace Application\Aftm\services;


use Application\Aftm\concrete\AftmFileManager;
use Application\Tops\services\TServiceCommand;

class GetDocumentListCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if ((empty($request))) {
            $this->addErrorMessage('No service request received.');
            return;
        }
        if (!isset($request->fileset)) {
            $this->addErrorMessage('List filter not received.');
            return;
        }

        $interval = isset($request->interval) ? $request->interval : 'month';
        $dateFormat = isset($request->dateformat) ? $request->dateformat : null;
        $list = AftmFileManager::GetFilesBySet($request->fileset,$interval,$dateFormat);
        $this->setReturnValue($list);
    }
}