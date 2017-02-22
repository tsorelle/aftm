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
    private function getFakeList()
    {
        $result = array();
        $item = new \stdClass();
        $item->date = '2015-01-02';
        $item->displayDate = 'January 2015';
        $item->title = "Title January 2015";
        $item->url = 'http://aftmbeta.2quakers.net/application/files/2714/8768/8107/aftm-minutes-2014-04.pdf';
        $result[] = $item;

        $item = new \stdClass();
        $item->date = '2015-09-13';
        $item->displayDate = 'September 2015';
        $item->title = "Title September 2015";
        $item->url = 'http://aftmbeta.2quakers.net/application/files/7714/8768/8108/aftm-minutes-2014-01.pdf';
        $result[] = $item;

        return $result;
    }

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
        // $this->addInfoMessage("File set: $request->fileset");
        // $list = $this->getFakeList();
        $list = AftmFileManager::GetFilesBySet($request->fileset);

        $this->setReturnValue($list);
    }
}