<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/25/2017
 * Time: 6:27 AM
 */

namespace Application\Aftm;


/**
 * Class AftmConfiguration
 * @package Application\Aftm
 *
 * Handles access to configuration file config.ini, located in same directory as this class file.
 * For ini file documentation see http://php.net/manual/en/function.parse-ini-file.php
 *
 */
class AftmConfiguration
{
    /**
     * @var array
     */
    private static $ini;

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
        $section = self::getSection($sectionKey);
        if (is_array($section) && array_key_exists($key,$section)) {
            return $section[$key];
        }
        return $defaultValue;
    }

    /**
     * Get array of values by section key
     *
     * @param $sectionKey
     * @return bool|mixed
     */
    public static function getSection($sectionKey)
    {
        $ini = self::getIni();
        if (array_key_exists($sectionKey,$ini)) {
            return $ini[$sectionKey];
        }
        return false;
    }

    public static function getEmailValues($key, $sectionKey) {
        $result = array();
        $keys = self::getValue($key,$sectionKey);
        if ($keys !== false) {
            $keys = explode(',', $keys);
            foreach ($keys as $key) {
                $key = trim($key);
                if (strstr($key,'@')) {
                    $email = $key;
                }
                else {
                    $email = self::getValue($key, 'email');
                }
                if (!empty($email)) {
                    $result[] = $email;
                }
            }
        }
        return $result;
    }

    /**
     * Parse the ini file, config.ini, located in same directory as this class file.
     * @return array
     */
    public static function getIni()
    {
        if (!isset(self::$ini)) {
            self::$ini = parse_ini_file(__DIR__.DIRECTORY_SEPARATOR."config.ini",true);
        }
        return self::$ini;
    }
}