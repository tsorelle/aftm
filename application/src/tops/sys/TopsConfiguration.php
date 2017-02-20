<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/20/2017
 * Time: 12:46 PM
 */

namespace Application\Tops\sys;


class TopsConfiguration
{
    /**
     * @var ConfigurationManager
     */
    private static $iniManager;

    /**
     * Retrieve value by key and section
     *
     * @param $key
     * @param $sectionKey
     * @param bool $defaultValue - returned if value does not exist
     * @return bool|mixed
     */
    public static function getValue($key, $sectionKey, $defaultValue = false)
    {
        return self::getIniManager()->getIniValue($key, $sectionKey, $defaultValue);
    }

    /**
     * Get array of values by section key
     *
     * @param $sectionKey
     * @return bool|mixed
     */
    public static function getSection($sectionKey)
    {
        return self::getIniManager()->getIniSection($sectionKey);
    }

    public static function getEmailValues($key, $sectionKey) {
       return self::getIniManager()->getIniEmailValues($key,$sectionKey);
    }


    /**
     * Parse the ini file, config.ini, located in same directory as this class file.
     * @return ConfigurationManager
     */
    public static function getIniManager()
    {
        if (!isset(self::$iniManager)) {
            $p = realpath(__DIR__.'\..');


            self::$iniManager = new ConfigurationManager($p.DIRECTORY_SEPARATOR."config.ini");
        }
        return self::$iniManager;
    }
}