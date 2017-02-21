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

    public function getBySet($setName,$interval=null,$dateFormat=null) {
        $result = array();
        $list = new \Concrete\Core\File\FileList();
        $set = \Concrete\Core\File\Set\Set::getByName($setName);
        if ($set === null) {
            return false;
        }
        $list->filterBySet($set);
        $list->sortByFilenameAscending();
        $files = $list->getResults();

        if (empty($dateFormat)) {
            $dateFormat = $interval == 'day' ? 'F j, Y' : 'F Y';
        }
        foreach ($files as $file) {
            $item = new \stdClass();
            $item->url = $file->getURL();
            $parts = explode('/',$item->url);
            $item->fileName = $parts[sizeof($parts) - 1];
            if ($interval) {
                $parts = explode('.',$item->fileName);
                $name = $parts[0];
                $parts = explode('-',$name);
                $length = sizeof($parts);
                if ($length > 3) {
                    if ($interval == 'day') {
                        $item->year = intval($parts[$length - 3]);
                        $item->month = intval($parts[$length - 2]);
                        $item->day = intval($parts[$length - 1]);
                    }
                    else {
                        $item->year = intval($parts[$length - 2]);
                        $item->month = intval($parts[$length - 1]);
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
            $result[] = $item;
        }
        return $result;
    }

    public static function GetFilesBySet($setName,$interval=null,$dateFormat=null) {
        return self::getInstance()->getBySet($setName,$interval,$dateFormat);
    }
}