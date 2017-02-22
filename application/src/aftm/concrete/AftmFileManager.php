<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/21/2017
 * Time: 5:44 AM
 */

namespace Application\Aftm\concrete;

class AftmFileManager
{
    /**
     * @var AftmFileManager
     */
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new AftmFileManager();
        }
        return self::$instance;
    }

    private function getFiles($setName) {
        $list = new \Concrete\Core\File\FileList();
        $set = \Concrete\Core\File\Set\Set::getByName($setName);
        if ($set === null) {
            return false;
        }
        $list->filterBySet($set);
        $list->sortByFilenameAscending();
        $files = $list->getResults();
        return $files;

    }

    private function getFileItem($file,$interval=null,$dateFormat=null, $rightOffset = 0) {
        $item = new \stdClass();
        $item->url = $file->getURL();
        $item->title = $file->getTitle();
        $item->fileName = $file->getFileName();
        if ($interval) {
            $parts = explode('.', $item->fileName);
            $name = $parts[0];
            $parts = explode('-', $name);
            $length = sizeof($parts);
            if ($length >= (2 + $rightOffset)) {
                if ($interval == 'day') {
                    $item->year = intval($parts[$length - (3 + $rightOffset)]);
                    $item->month = intval($parts[$length - (2 + $rightOffset)]);
                    $item->day = intval($parts[$length - (1 + $rightOffset)]);
                } else {
                    $item->year = intval($parts[$length - (2 + $rightOffset)]);
                    $item->month = intval($parts[$length - (1 + $rightOffset)]);
                    $item->day = 1;
                }

                if ($item->year > 0 && $item->month > 0 && $item->day > 0) {
                    $time = mktime(0, 0, 0, $item->month, $item->day, $item->year);
                    if ($time !== false) {
                        $item->date = date('Y-m-d', $time);
                        $item->displayDate = date($dateFormat, $time);
                    }
                }
            }
        }
        return $item;
    }

    public function getNewsletters() {
        $result = array();
        $files = $this->getFiles('Newsletters');
        $dateFormat = 'F Y';

        foreach ($files as $file) {
            $item = $this->getFileItem($file,'month',$dateFormat);
            $name = str_ireplace('.pdf','',$item->fileName);
            $parts = explode('-',$name);
            $length = sizeof($parts);
            if ($length > 4) {
                $vol = $parts[$length - 4];
                $num = $parts[$length - 3];
                $item->title = "Volume $vol Number $num, ".$item->displayDate;
            }
            else {
                $item->title = $item->displayDate;
            }

            $result[] = $item;
        }
        return $result;
    }

    public function getMinutes() {
        $result = array();
        $files = $this->getFiles('Minutes');
        $dateFormat = 'F Y';

        foreach ($files as $file) {
            $item = $this->getFileItem($file,'month',$dateFormat);
            $item->title = "Minutes, $item->displayDate";
            $result[] = $item;
        }
        return $result;
    }

    public function getFinancials() {
        $result = array();
        $files = $this->getFiles('Financial');
        $dateFormat = 'F Y';

        if (empty($files)) {
            return $result;
        }

        foreach ($files as $file) {
            $item = $this->getFileItem($file,'month',$dateFormat,1);
            if ($item->title == $item->fileName) {
                $name = str_ireplace('.pdf','',$item->fileName);
                $parts = explode('-',$name);
                $length = sizeof($parts);
                $type = $parts[$length-1];
                switch (strtolower($type)) {
                    case 'inc' :
                        $item->title = "Income & Expense, $item->displayDate";
                        break;
                    case 'bal' :
                        $item->title = "Balance Sheet, $item->displayDate";
                        break;
                    case 'ytd':
                        $item->title = "Year to Date, $item->displayDate";
                        break;
                    case 'pl':
                        $item->title = "Profit and Loss, $item->displayDate";
                        break;
                    default :
                        $item->title = "$type, $item->displayDate";
                        break;
                }
            }
            $result[] = $item;
        }
        return $result;
    }



    public function getBySet($setName,$interval=null,$dateFormat=null, $rightOffset = 0) {
        $result = array();
        $files = $this->getFiles($setName);
        if (empty($dateFormat)) {
            $dateFormat = $interval == 'day' ? 'F j, Y' : 'F Y';
        }

        foreach ($files as $file) {
            $item = $this->getFileItem($file,$interval,$dateFormat,$rightOffset);
            $result[] = $item;
        }
        return $result;
    }

    public static function GetFilesBySet($setName,$interval=null,$dateFormat=null, $rightOffset = 0) {
        $mgr = self::getInstance();
        switch ($setName) {
            case 'Minutes' : return $mgr->getMinutes();
            case 'Newsletters' : return $mgr->getNewsletters();
            case 'Financial' : return $mgr->getFinancials();
        }
        return self::getInstance()->getBySet($setName,$interval,$dateFormat,$rightOffset);
    }
}